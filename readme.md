# Synchole

## Installation

### install github app

#### Settings / Developer settings / New GitHub App

* Webhook URL
  * https://{your-domain}/api/github/webhook
* Permissions
  * Repository contents: Read-only
  * Pull requests : Read-only
* Subscribe to events
  * Pull request
* create
* memo app_id
* Generate a private key

#### Settings / Developer settings / GitHub Apps / Install App
* select install repos
  
### google oauth setting
* https://{your-domain}/auth/login/callback

### build
```bash
make build
```

### edit .env file
```dotenv
MAIN_DOMAIN={your domain}
EMAIL={your email for ssl registration}

GITHUB_APP_ID={github app_id}
GITHUB_USERNAME={user or orgs}
GITHUB_APP_KEY={github app generated private key file name}

GOOGLE_CLIENT_ID={google oauth client_id}
GOOGLE_APP_SECRET={google oauth secret}
```

### deploy
```bash
make deploy
```

### test
* create new repository
* make Dockerfile
* make synchole.yml (docker stack)
```yaml
version: '3.3'

services:
  whoami:
    image: whoami
    deploy:  
      labels:
        - synchole.build=Dockerfile
        - synchole.domain.enable=true
        - synchole.domain.prefix=whoami
        - synchole.port=80
  
  whoareyou:
    image: whoareyou
    networks:
      - test
    deploy:
      labels:
        - synchole.build=Dockerfile
        - synchole.domain.enable=true
        - synchole.port=80
        - synchole.auth.gate.enable=true

      replicas: 2
      resources:
        limits:
          cpus: '0.4'
          memory: '20M'
  
  redis:
    image: redis    
    networks:
      - test
    deploy:
      labels:
        - synchole.domain.enable=false

networks:
  test:
```
* git push
* create pull request
