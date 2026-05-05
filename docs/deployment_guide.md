#  GUIDE DE DÉPLOIEMENT - Symfony Authentication Platform



## 1. ARCHITECTURE DE DÉPLOIEMENT

L'application utilise **Docker Compose** pour orchestrer 6 services interconnectés :

```
┌─────────────────────────────────────────────────┐
│         ENVIRONNEMENT DOCKER                    │
├─────────────────────────────────────────────────┤
│                                                 │
│  ┌──────────────┐  ┌──────────────┐  ┌───────┐ │
│  │ PHP 8.3      │  │ MySQL 8.0    │  │Mailhog│ │
│  │ (Apache)     │  │ (Database)   │  │(Email)│ │
│  │ Port 8080    │  │ Port 3307    │  │8025   │ │
│  └──────────────┘  └──────────────┘  └───────┘ │
│                                                 │
│  ┌──────────────┐  ┌──────────────┐  ┌───────┐ │
│  │ Mercure      │  │ Fail2Ban     │  │ Code  │ │
│  │ (Real-time)  │  │ (Sécurité)   │  │ Source│ │
│  │ Port 3000    │  │ Monitoring   │  │(Volume)
│  └──────────────┘  └──────────────┘  └───────┘ │
│                                                 │
└─────────────────────────────────────────────────┘
```

### Services :

| Service | Rôle | Port | Base de déploiement |
|---------|------|------|-------------------|
| **php-apache** | Application Symfony | 8080 | `Dockerfile` |
| **db** | Base de données MySQL | 3307 | Image `mysql:8.0` |
| **mailhog** | Serveur SMTP test | 8025/1025 | Image `mailhog/mailhog` |
| **fail2ban** | Protection brute-force | - | Image `crazymax/fail2ban` |
| **mercure** | WebSocket real-time | 3000 | Image `dunglas/mercure` |

---

## 2. PRÉPARATION DE L'ENVIRONNEMENT

### 2.1 Fichier `.env` (Secrets de dev)

Le fichier `.env` contient les secrets **développement UNIQUEMENT** :

```dotenv
# Framework Symfony
APP_ENV=dev                    # ← CHANGER EN "prod" pour production
APP_DEBUG=1                    # ← CHANGER EN "0" pour production
APP_SECRET=8a3c71b2f4e5d6a...

# Base de données
MYSQL_ROOT_PASSWORD=root       # ← CHANGER en mot de passe fort
MYSQL_DATABASE=symfony-auth-platform
MYSQL_USER=root
MYSQL_PASSWORD=root
DATABASE_URL="mysql://root:root@db:3306/symfony-auth-platform?serverVersion=8.0"

# Email (MailHog en dev, Mailgun en prod)
MAILER_DSN=smtp://127.0.0.1:1025

# Real-time (Mercure)
MERCURE_PUBLISHER_JWT_KEY=your_secret_key_here
MERCURE_SUBSCRIBER_JWT_KEY=your_secret_key_here
```

### 2.2 Variables d'environnement PRODUCTION

Pour la production, créez `.env.prod.local` (non versionné) :

```dotenv
# Framework
APP_ENV=prod
APP_DEBUG=0
APP_SECRET=<générer avec: php bin/console secrets:generate>

# Base de données - CHANGER LES IDENTIFIANTS
MYSQL_ROOT_PASSWORD=<password_forte_aleatoire>
MYSQL_DATABASE=app_prod
MYSQL_USER=app_user
MYSQL_PASSWORD=<password_forte_aleatoire>
DATABASE_URL="mysql://app_user:<password_forte@db:3306/app_prod?serverVersion=8.0"

# Email - Utiliser Mailgun en prod (ou service réel)
MAILER_DSN=smtp://smtp.mailgun.org:587?encryption=tls&username=<mailgun_user>&password=<mailgun_password>

# Mercure - URLs absolues
MERCURE_URL=https://votre-domaine.com/.well-known/mercure
MERCURE_PUBLISHER_JWT_KEY=<clé_sécurisée>
MERCURE_SUBSCRIBER_JWT_KEY=<clé_sécurisée>
```

** JAMAIS versionner `.env.prod.local` ou `.env.local` !**

---

## 3. DÉPLOIEMENT PAR ÉTAPES

### 3.1 BUILD DE L'IMAGE DOCKER

Construction de l'image PHP-Apache custom :

```bash
# Construction de l'image (first time ou après modif du Dockerfile)
docker-compose build php-apache

# Alternative : build tout
docker-compose build
```


1. Lecture du `Dockerfile`
2. Installation de PHP 8.3-apache
3. Installation des extensions : `pdo_mysql`, `gd`, `intl`, `zip`, `opcache`
4. Configuration du VirtualHost Apache (`vhost.conf`)
5. Installation de Composer
6. Image prête à être utilisée

### 3.2 DÉMARRAGE DES SERVICES

```bash
# Démarrage tous les services en arrière-plan (-d = detached)
docker-compose up -d

# Vérifier que tous les services sont UP
docker-compose ps

# La sortie devrait ressembler à :
# NAME              STATUS        PORTS
# symfony_app       Up 2 minutes  0.0.0.0:8080->80/tcp
# symfony_db        Up 2 minutes  0.0.0.0:3307->3306/tcp
# mailhog           Up 2 minutes  0.0.0.0:8025->8025/tcp, 0.0.0.0:1025->1025/tcp
# symfony_fail2ban  Up 2 minutes  (health: starting)
# mercure           Up 2 minutes  0.0.0.0:3000->80/tcp
```

### 3.3 INSTALLEZ LES DÉPENDANCES PHP

```bash
# Option 1 : Dépendances de production (RECOMMANDÉ pour prod)
docker exec symfony_app composer install --no-dev --optimize-autoloader

# Option 2 : Dépendances complètes (DEV seulement)
docker exec symfony_app composer install
```

### 3.4 EXÉCUTEZ LES MIGRATIONS

**Les migrations = mise à jour du schéma de base de données**

```bash
# Voir les migrations à exécuter
docker exec symfony_app php bin/console doctrine:migrations:status

# Exécuter les migrations
docker exec symfony_app php bin/console doctrine:migrations:migrate --no-interaction

# Output expect :
# ++ 20260429151048 > App\\Migrations\\Version20260429151048
# ++ 20260501192157 > App\\Migrations\\Version20260501192157
# ++ 20260503073402 > App\\Migrations\\Version20260503073402
# ++ 20260503091341 > App\\Migrations\\Version20260503091341
# ++++ 4 migrations executed
```

**Que font ces migrations :**
- `Version20260429151048` : Création des tables (users, conversations, messages, etc.)
- `Version20260501192157` : Ajout des indexs
- `Version20260503073402` : Modifications de schéma
- `Version20260503091341` : Contraintes additionnelles

### 3.5 NETTOYEZ LE CACHE

```bash
# Effacer le cache Symfony
docker exec symfony_app php bin/console cache:clear --env=prod

# Cache cleared for environment: prod
```

### 3.6 VÉRIFIEZ LA SANTÉ DE L'APPLICATION

```bash
# Test HTTP basique
curl -I http://localhost:8080

# Doit retourner 200 OK (pas 500 error)

# Test Mercure (real-time)
curl http://localhost:3000/healthz

# Test MySQL
docker exec symfony_db mysql -u root -proot -e "SHOW DATABASES;"

# Test MailHog interface web
# Ouvrir : http://localhost:8025 (pour voir les emails envoyés)
```

---

## 4. PROCÉDURES DE DÉPLOIEMENT

### 4.1 DÉPLOIEMENT INITIAL (Premier déploiement)

```bash
#!/bin/bash
# script: deploy-init.sh

set -e  # Exit on error

echo "===  DÉPLOIEMENT INITIAL ==="

# 1. Clone du repo
git clone <your-repo> symfony-app
cd symfony-app

# 2. Copier .env (non versionné)
cp .env
#  ÉDITER .env avec les vrais secrets

# 3. Build et démarrage
docker-compose up -d --build

# 4. Installation des dépendances
docker exec symfony_app composer install --no-dev --optimize-autoloader

# 5. Migrations
docker exec symfony_app php bin/console doctrine:migrations:migrate --no-interaction

# 6. Nettoyage
docker exec symfony_app php bin/console cache:clear --env=prod

echo " Déploiement initial terminé"
echo " App accessible sur http://localhost:8080"
echo " Mailhog disponible sur http://localhost:8025"
```

### 4.2 DÉPLOIEMENT DE MISE À JOUR (Pull de code + migrations)

```bash
#!/bin/bash
# script: deploy-update.sh

set -e

echo "===  MISE À JOUR DE L'APPLICATION ==="

# 1. Pull du dernier code
git pull origin main

# 2. Rebuild image (si Dockerfile modifié)
docker-compose build php-apache

# 3. Redémarrer les services
docker-compose up -d

# 4. Maj des dépendances (optionnel)
docker exec symfony_app composer install --no-dev --optimize-autoloader

# 5. Migrations
docker exec symfony_app php bin/console doctrine:migrations:migrate --no-interaction

# 6. Nettoyage cache
docker exec symfony_app php bin/console cache:clear --env=prod

# 7. Warm cache (optionnel mais recommandé)
docker exec symfony_app php bin/console cache:warmup

echo " Mise à jour terminée"
```

### 4.3 ROLLBACK D'UNE MIGRATION (En cas de problème)

```bash
# Voir le statut des migrations
docker exec symfony_app php bin/console doctrine:migrations:status

# Rollback d'une migration
docker exec symfony_app php bin/console doctrine:migrations:migrate prev --no-interaction

# Ou rollback jusqu'à une version précise :
docker exec symfony_app php bin/console doctrine:migrations:migrate --to=20260501192157 --no-interaction

# Vérifier le statut après rollback
docker exec symfony_app php bin/console doctrine:migrations:status
```

---

## 5. MAINTENANCE ET MONITORING

### 5.1 Logs en temps réel

```bash
# Tous les logs
docker-compose logs -f

# Logs d'un service spécifique
docker-compose logs -f php-apache
docker-compose logs -f db
docker-compose logs -f fail2ban

# Historique des logs
docker-compose logs --tail 100 php-apache
```

### 5.2 Accès à la base de données

```bash
# Client MySQL interactif
docker exec -it symfony_db mysql -u root -proot

# Exécuter une requête
docker exec -it symfony_db mysql -u root -proot pygame -e "SELECT * FROM user LIMIT 5;"

# Dump de la BD
docker exec symfony_db mysqldump -u root -proot symfony-auth-platform > backup.sql

# Restaurer un dump
docker exec -i symfony_db mysql -u root -proot symfony-auth-platform < backup.sql
```

### 5.3 Redémarrage des services

```bash
# Redémarrer UN service
docker-compose restart php-apache

# Redémarrer TOUS les services
docker-compose restart

# Arrêter les services (conservation des volumes)
docker-compose stop

# Arrêter et supprimer les conteneurs
docker-compose down

# Arrêter et supprimer WITH volumes (attention = perte de données)
docker-compose down -v
```

### 5.4 Surveillance Fail2Ban (Sécurité)

```bash
# Logs de Fail2Ban
docker-compose logs fail2ban

# Status des règles de ban
docker exec symfony_fail2ban fail2ban-client status

# Débannir une IP
docker exec symfony_fail2ban fail2ban-client set sshd unbanip <IP>
```

---

## 6. SÉCURITÉ EN PRODUCTION

### 6.1 Checklist avant prod

- [ ] `APP_ENV=prod` dans `.env`
- [ ] `APP_DEBUG=0` (JAMAIS 1 en production)
- [ ] `APP_SECRET` changé (random + long)
- [ ] Mots de passe MySQL strongs (30+ chars aléatoire)
- [ ] `.env.prod.local` dans `.gitignore` (JAMAIS versionné)
- [ ] HTTPS configuré (Caddy/Let's Encrypt)
- [ ] Fail2Ban actif et configuré
- [ ] Backups automatiques de la BD
- [ ] Logs centralisés (ELK, DataDog, etc.)

### 6.2 Configuration HTTPS (Mercure + Apache)

Mercure et Apache doivent être en HTTPS en production :

```yaml
# docker-compose.yml - Service Mercure avec HTTPS
mercure:
  environment:
    SERVER_NAME: 'votre-domaine.com:443'  # HTTPS
    MERCURE_PUBLISHER_JWT_KEY: ${MERCURE_PUBLISHER_JWT_KEY}
    MERCURE_SUBSCRIBER_JWT_KEY: ${MERCURE_SUBSCRIBER_JWT_KEY}
  # Certificats SSL Let's Encrypt via Caddy
  volumes:
    - ./caddy_data:/data
    - ./caddy_config:/config
```

### 6.3 Firewall et ports

```bash
# Exposer UNIQUEMENT les ports nécessaires :
# - Port 80 (HTTP) → redirection vers 443
# - Port 443 (HTTPS) 
# - Port 3307 (MySQL) → JAMAIS accessible de l'extérieur (local only)
# - Port 8025 (Mailhog) → JAMAIS accessible de l'extérieur (local only)

# Exemple iptables (Linux)
iptables -A INPUT -p tcp --dport 80 -j ACCEPT
iptables -A INPUT -p tcp --dport 443 -j ACCEPT
iptables -A INPUT -p tcp --dport 3307 -j DROP
```

---

## 7. VARIABLES D'ENVIRONNEMENT COMPLÈTES

```dotenv
### DÉVELOPPEMENT (.env)
APP_ENV=dev
APP_DEBUG=1
APP_SECRET=unsecure_secret_dev_only

MYSQL_ROOT_PASSWORD=root
MYSQL_DATABASE=symfony-auth-platform
MYSQL_USER=root
DATABASE_URL="mysql://root:root@db:3306/symfony-auth-platform?serverVersion=8.0"

MAILER_DSN=smtp://127.0.0.1:1025
MERCURE_PUBLISHER_JWT_KEY=dev_key
MERCURE_SUBSCRIBER_JWT_KEY=dev_key

### PRODUCTION (.env.prod.local - ⚠️ NON VERSIONNÉ)
APP_ENV=prod
APP_DEBUG=0
APP_SECRET=<généré avec php bin/console secrets:generate>

MYSQL_ROOT_PASSWORD=<pwd_strong_30chars>
MYSQL_DATABASE=app_prod
MYSQL_USER=app_user
DATABASE_URL="mysql://app_user:<pwd>@db:3306/app_prod?serverVersion=8.0&charset=utf8mb4"

MAILER_DSN=smtp://smtp.mailgun.org:587?encryption=tls&username=<mail>&password=<pwd>

MERCURE_URL=https://votre-domaine.com
MERCURE_PUBLISHER_JWT_KEY=<jwt_key_issued>
MERCURE_SUBSCRIBER_JWT_KEY=<jwt_key_issued>
```

---

## 8. STRUCTURE DES FICHIERS DE DÉPLOIEMENT

```
symfony-authentication-platform/
├── .env                    # ← Variables dev (versionné)
├── .env.prod.local         # ← Variables prod ( NON versionné, .gitignore)
├── docker-compose.yml      # ← Orchestration (versionné)
├── Dockerfile              # ← Image PHP (versionné)
├── vhost.conf              # ← Config Apache (versionné)
├── migrations/             # ← Scripts de migration (versionné)
│   ├── Version20260429151048.php
│   └── ...
├── scripts/
│   ├── deploy-init.sh      # ← First time deployment
│   ├── deploy-update.sh    # ← Update deployment
│   └── rollback.sh         # ← Rollback procedure
└── README-DEPLOY.md        # ← Cette documentation
```

---

## 9. COMMANDES RAPIDES (CHEATSHEET)

```bash
# 🚀 DÉPLOYER
docker-compose up -d && docker exec symfony_app php bin/console doctrine:migrations:migrate

# 📊 VÉRIFIER LA SANTÉ
docker-compose ps
docker-compose logs -f

# 💾 BACKUP BD
docker exec symfony_db mysqldump -u root -proot symfony-auth-platform > backup_$(date +%Y%m%d_%H%M%S).sql

# ⚙️ REDÉMARRER
docker-compose restart

# 🧹 NETTOYER
docker-compose down -v

# 🔍 DEBUG
docker exec -it symfony_app bash
```

---

## 10. SCENARIO : DÉPLOIEMENT EN PRODUCTION (Pas à pas)

### Jour 1 : Setup initial

```bash
# 1. SSH sur le serveur de prod
ssh user@production-server.com

# 2. Clone du repo
git clone https://github.com/your-repo/symfony-auth.git
cd symfony-auth

# 3. Créer .env.prod.local avec vrais secrets
cat > .env.prod.local << 'EOF'
APP_ENV=prod
APP_DEBUG=0
APP_SECRET=$(php bin/console secrets:generate)
MYSQL_ROOT_PASSWORD=RandomStr0ng!Pass#123
...
EOF

# 4. Démarrer
docker-compose up -d --build

# 5. Setup initial
docker exec symfony_app composer install --no-dev --optimize-autoloader
docker exec symfony_app php bin/console doctrine:migrations:migrate --no-interaction
docker exec symfony_app php bin/console cache:clear --env=prod

# ✅ Application live !
```

### Jour 30 : Mise à jour version 2.0

```bash
# 1. Pull du code
git pull origin main

# 2. Build changes
docker-compose build

# 3. Redémarrer services
docker-compose up -d

# 4. Migrations nécessaires exécutées auto
docker exec symfony_app php bin/console doctrine:migrations:migrate --no-interaction

# ✅ Version 2.0 live
```

### Jour 31 : Bug trouvé, rollback d'une migration

```bash
# 1. Identifier le problème
docker-compose logs php-apache | tail -100

# 2. Rollback de la dernière migration
docker exec symfony_app php bin/console doctrine:migrations:migrate prev

# 3. Redémarrer pour être sûr
docker-compose restart php-apache

# ✅ Revenu à état stable
```

---

##  Ressources pour en savoir plus

- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [Symfony Deployment](https://symfony.com/doc/current/deployment.html)
- [Doctrine Migrations](https://symfony.com/doc/current/doctrine/migrations.html)
- [Mercure Documentation](https://mercure.rocks/)

---

**Dernière mise à jour : 3 mai 2026**  
**Version de guide : 1.0**