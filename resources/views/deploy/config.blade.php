version: '3.3'

services:
  traefik:
    image: traefik:1.7.11
    command:
      - "--api"
      - "--docker"
      - "--docker.watch"
      - "--docker.domain={{$MAIN_DOMAIN}}"
      - "--docker.swarmMode=true"
      - "--ping"
      - "--ping.entryPoint=http"
      - "--entryPoints=Name:http Address::80 Redirect.EntryPoint:https"
      - "--entryPoints=Name:https Address::443 TLS"
      - "--acme"
      - "--acme.email={{$EMAIL}}"
      - "--acme.domains={{$MAIN_DOMAIN}}"
      - "--acme.storage=acme.json"
      - "--acme.entryPoint=https"
      - "--acme.onHostRule=true"
      - "--acme.httpChallenge.entryPoint=http"
      - "--retry"
@if($LOG_LEVEL=='debug')
      - "--loglevel=DEBUG"
@endif
    ports:
      - "80:80"
      - "443:443"

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
        - traefik.default.port=8080
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
      - /etc/synchole/data:/app/data
      - .env:/app/synchole/.env
      - /var/run/docker.sock:/var/run/docker.sock
    networks:
      - web
    deploy:
      labels:
        - traefik.enable=true
        - traefik.default.port=80
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
      - /etc/synchole/portainer:/app/data

    networks:
      - web

    deploy:
      labels:
        - traefik.enable=true
        - traefik.default.port=9000
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