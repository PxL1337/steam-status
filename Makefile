.DEFAULT_GOAL := help

## 🔧 Construire les conteneurs Docker (prod, standalone)
build: ## Build des conteneurs en mode prod
	docker compose -f docker-compose.prod.yml build

## 🚀 Lancer l'application avec DB intégrée
up: ## Lancer l'application en mode standalone
	docker compose -f docker-compose.prod.yml up -d

## 🔌 Lancer l'application avec DB externe
up-ext: ## Lancer avec base de données externe
	docker compose -f docker-compose.external-db.yml up -d

## 🛑 Arrêter les conteneurs
down: ## Stopper tous les conteneurs
	docker compose down

## 🧹 Nettoyer les volumes, conteneurs, cache
clean: ## Supprimer les conteneurs, volumes et cache
	docker compose down -v --remove-orphans

## 🐚 Accès au conteneur principal
bash: ## Ouvrir un shell dans le conteneur app
	docker exec -it steam-status-app sh

help: ## Affiche cette aide
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-16s\033[0m %s\n", $$1, $$2}'
