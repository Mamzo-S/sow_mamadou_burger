# DevOps Laravel + Jenkins

Cette configuration automatise les points demandes :

- recuperation du code depuis GitHub sur la branche `sow_mamadou_burger`
- installation des dependances Laravel avec Composer
- creation d une image Docker de l application
- declenchement automatique par webhook GitHub vers Jenkins local

## 1. Branche GitHub

Le depot a ete prepare sur la branche :

`sow_mamadou_burger`

Commandes utiles :

```bash
git switch sow_mamadou_burger
git add Jenkinsfile Dockerfile .dockerignore docs/devops-jenkins.md
git commit -m "Add Jenkins pipeline and Docker image"
git push -u origin sow_mamadou_burger
```

## 2. Configuration Jenkins

Dans Jenkins local :

1. Installer les plugins `Git`, `GitHub` et `Pipeline`.
2. Creer un job `Pipeline`.
3. Cocher `GitHub hook trigger for GITScm polling`.
4. Dans `Pipeline`, choisir `Pipeline script from SCM`.
5. SCM : `Git`
6. Repository URL : `https://github.com/Mamzo-S/sow_mamadou_burger.git`
7. Branch Specifier : `*/sow_mamadou_burger`
8. Script Path : `Jenkinsfile`

## 3. Webhook GitHub vers Jenkins local

GitHub ne peut pas appeler directement `localhost`. Pour un Jenkins local, expose Jenkins avec `ngrok`.

Exemple :

```bash
ngrok http 8080
```

Puis dans GitHub :

1. Ouvrir `Settings > Webhooks > Add webhook`
2. Payload URL : `https://votre-url-ngrok/github-webhook/`
3. Content type : `application/json`
4. Choisir `Just the push event`
5. Enregistrer

Webhook Jenkins a utiliser :

`/github-webhook/`

## 4. Ce que fait le Jenkinsfile

- clone la branche GitHub cible
- cree `.env` si besoin
- cree `database/database.sqlite` si besoin
- lance `composer install`
- construit l image Docker `isi-burger`

## 5. Build Docker manuel

Si tu veux tester sans Jenkins :

```bash
docker build -t isi-burger:local .
docker run --rm -p 8000:8000 isi-burger:local
```

## 6. Lancement avec Docker Compose

Le fichier [docker-compose.yml](C:\Users\hp\Documents\ISI\Licence 3\PHP\Exam\isi_burger\docker-compose.yml) permet de lancer l application plus vite :

```bash
docker compose up --build
```

Puis ouvrir :

`http://localhost:8000`
