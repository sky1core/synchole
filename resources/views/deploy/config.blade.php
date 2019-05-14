version: '3.3'

services:
  traefik:
    image: traefik:1.7
    command:
@if($LOG_LEVEL=='debug')
      - "--loglevel=DEBUG"
@endif
      - "--defaultEntryPoints={{$PROTOCOLS}}"
      - "--api"
      - "--docker"
      - "--docker.watch=true"
      - "--docker.domain={{$MAIN_DOMAIN}}"
      - "--docker.swarmMode=true"
      - "--docker.exposedByDefault=false"
      - "--ping"
      - "--ping.entryPoint=http"
@if($REDIRECT_TO_HTTPS)
      - "--entryPoints=Name:http Address::80 Redirect.EntryPoint:https"
@else
      - "--entryPoints=Name:http Address::80"
@endif
@if($USE_HTTPS)
      - "--entryPoints=Name:https Address::443 TLS"
      - "--acme"
      - "--acme.acmeLogging=true"
      - "--acme.email={{$EMAIL}}"
      - "--acme.domains={{$MAIN_DOMAIN}}"
      - "--acme.storage=acme.json"
      - "--acme.entryPoint=https"
      - "--acme.onHostRule=true"
      - "--acme.httpChallenge"
      - "--acme.httpChallenge.entryPoint=http"
@if($ACME_CA_SERVER)
      - "--acme.caServer={{$ACME_CA_SERVER}}"
@endif
@endif
      - "--retry"

    ports:
      - "80:80"
@if($USE_HTTPS)
      - "443:443"
@endif
    volumes:
      - /var/run/docker.sock:/var/run/docker.sock
      - /etc/synchole/acme.json:/acme.json

    networks:
      - web

    deploy:
      labels:
        - traefik.docker.network=main_web
        - traefik.enable=true
        - traefik.protocol=http
        - traefik.port=8080
        - traefik.frontend.entryPoints={{$PROTOCOLS}}
        - "traefik.frontend.rule=Host:traefik.{{$MAIN_DOMAIN}}"
        - traefik.backend=traefik
@if($USE_GOOGLE_AUTH)
        - "traefik.frontend.auth.forward.address=http://synchole/auth/gate/admin"
        - "traefik.frontend.auth.forward.authResponseHeaders=X-Auth-User"
@endif

      placement:
        constraints:
          - node.role==manager

  synchole:
    image: synchole:latest

@if($GITHUB_APP_KEY)
    secrets:
      - github_app_key
@endif

    volumes:
@if($MOUNT_DEV)
      - .:/app/synchole
      - /app/synchole/storage/framework/sessions/
      - /app/synchole/storage/framework/cache/data/
      - /app/synchole/storage/framework/views
@endif
      - ./.env:/app/synchole/.env
      - /etc/synchole/data:/etc/synchole/data
      - /var/run/docker.sock:/var/run/docker.sock
    networks:
      - web
    deploy:
      labels:
        - traefik.docker.network=main_web
        - traefik.enable=true
        - traefik.port=80
        - traefik.protocol=http
        - traefik.frontend.entryPoints={{$PROTOCOLS}}
        - "traefik.frontend.rule=Host:{{$MAIN_DOMAIN}}"
        - traefik.backend=synchole

      placement:
        constraints:
          - node.role==manager

  redis:
    image: redis

    networks:
      - web

    deploy:

  portainer:
    image: portainer/portainer:1.20.2

    volumes:
      - /var/run/docker.sock:/var/run/docker.sock
      - /etc/synchole/portainer:/data

    networks:
      - web

    deploy:
      labels:
        - traefik.docker.network=main_web
        - traefik.enable=true
        - traefik.port=9000
        - traefik.protocol=http
        - traefik.frontend.entryPoints={{$PROTOCOLS}}
        - "traefik.frontend.rule=Host:portainer.{{$MAIN_DOMAIN}}"
        - traefik.backend=portainer
@if($USE_GOOGLE_AUTH)
        - "traefik.frontend.auth.forward.address=http://synchole/auth/gate/admin"
        - "traefik.frontend.auth.forward.authResponseHeaders=X-Auth-User"
@endif


      placement:
        constraints:
          - node.role==manager

networks:
  web:

secrets:
@if($GITHUB_APP_KEY)
  github_app_key:
    file: {{$GITHUB_APP_KEY}}
@endif