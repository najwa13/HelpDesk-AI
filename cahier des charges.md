HelpDesk AI 

1. Présentation du projet
HelpDesk AI est une application web de gestion de tickets de support, destinée à une entreprise éditrice de logiciels SaaS. Les clients signalent des bugs, des problèmes de connexion ou posent des questions fonctionnelles.
L'intelligence artificielle intervient à deux niveaux distincts. D'abord, une recherche automatique dans la base de connaissances : si un article validé répond à la demande, il est envoyé directement au client, sans création de ticket. Sinon, un ticket est créé et un agent le traite, assisté par l'IA (résumé, catégorie, priorité, brouillon de réponse).
Dans les deux cas, aucun texte n'est généré à la volée pour le client : soit il reçoit un article rédigé et validé par un administrateur, soit un agent valide personnellement la réponse avant envoi. L'IA reste un outil d'aide à la décision, jamais un émetteur autonome de contenu.
Le projet est mené comme un développement backend réel : cadrage, conception, développement Laravel, tests, conteneurisation, intégration continue, déploiement et documentation.
2. Contexte et problématique 
Dans une entreprise SaaS, une grande partie des demandes de support concerne des problèmes déjà connus et documentés (mot de passe oublié, erreur de connexion classique...). Pourtant, un agent doit souvent rechercher la solution puis la retaper manuellement pour chaque client.
Pour les demandes plus complexes, un travail d'analyse reste nécessaire : lecture, catégorisation, évaluation de la priorité, rédaction d'une réponse — un processus répétitif qui ralentit le traitement en période de forte affluence.
HelpDesk AI répond à ces deux problèmes séparément : une recherche automatique dans la base de connaissances traite les demandes déjà documentées, tandis qu'une IA consultative assiste l'agent sur les demandes qui nécessitent une intervention humaine. Dans les deux cas, le contenu envoyé au client reste toujours écrit ou validé par un humain.
L'IA intervient également comme outil de développement (assistant de code), avec la même exigence : chaque ligne livrée doit rester comprise et explicable.

3. Objectifs fonctionnels
•      Centraliser les demandes de support d'une entreprise SaaS.
•      Réduire le nombre de tickets répétitifs grâce à une base de connaissances consultée automatiquement.
•      Répondre sans délai aux demandes déjà couvertes par un article validé.
•      Faciliter le traitement des tickets nécessitant une intervention humaine.
•      Réduire le temps d'analyse d'un ticket grâce à l'IA.
•      Organiser les tickets par statut, catégorie et priorité.
•      Améliorer le suivi des échanges entre clients et agents.
•      Fournir un tableau de bord de suivi, incluant le taux de résolution automatique.
4. Acteurs
Trois profils interagissent avec l'application, chacun avec un niveau d'accès distinct.
Acteur
Rôle dans l'application
Administrateur
Gère les comptes agents les catégories et les articles ; consulte le tableau de bord global.
Agent de support
Consulte, traite et répond aux tickets ; sollicite et valide l'assistance IA.
Client
Soumet une demande, consulte les réponses et échange avec le support. 

 
L'IA n'est pas un acteur du système : c'est un service interne, déclenché par l'agent, sans accès direct au client.



5. User stories
US1 : Authentification
•      En tant qu'utilisateur, je souhaite me connecter afin d'accéder à mon espace.
•      En tant qu'utilisateur, je souhaite me déconnecter afin de sécuriser ma session.
US2 : Gestion des tickets
•      En tant que client, je souhaite créer un ticket afin de signaler un problème.
•      En tant que client, je souhaite consulter mes tickets afin de suivre leur traitement.
•      En tant qu'agent, je souhaite consulter la liste des tickets afin de traiter les demandes.
•      En tant qu'agent, je souhaite modifier le statut d'un ticket afin de suivre son avancement.
•      En tant qu'administrateur, je souhaite affecter un ticket à un agent afin de répartir les demandes.

US3 : Réponses
•      En tant qu'agent, je souhaite répondre à un ticket afin d'aider le client.
•      En tant que client, je souhaite répondre à un agent afin d'apporter des informations complémentaires.
•      En tant qu'utilisateur, je souhaite consulter l'historique des échanges afin de suivre la conversation.

US4 : Catégories 
•      En tant qu'administrateur, je souhaite gérer les catégories afin d'organiser les tickets.

US5 : Base de connaissance

•      En tant qu'administrateur, je souhaite créer un article afin de documenter une solution fréquente.
•      En tant qu'administrateur, je souhaite modifier ou supprimer un article afin de maintenir l'information à jour.
•      En tant que client, je souhaite consulter les articles d'aide afin de résoudre un problème sans créer de ticket.
•      En tant que système, je souhaite rechercher un article correspondant à une demande afin de répondre automatiquement lorsque c'est possible.

US6 : Assistance IA
•      En tant qu'agent, je souhaite lancer une analyse IA afin d'obtenir un résumé du ticket.
•      En tant qu'agent, je souhaite recevoir une catégorie et une priorité proposées afin de traiter le ticket plus vite.
•      En tant qu'agent, je souhaite recevoir un brouillon de réponse afin de gagner du temps de rédaction.
•      En tant qu'agent, je souhaite modifier la réponse proposée avant son envoi.
US7 : Tableau de bord
•      En tant qu'administrateur, je souhaite consulter les statistiques des tickets afin de suivre l'activité du support.
•      En tant qu'administrateur, je souhaite consulter le nombre de demandes résolues automatiquement par la base de connaissances.
•      En tant qu'administrateur, je souhaite identifier les catégories de tickets les plus fréquentes.

US8 : Assistant IA conversationnel agent: 

En tant qu'agent, je souhaite discuter avec l'assistant IA afin d'obtenir une aide complémentaire sur un ticket.
En tant qu'agent, je souhaite demander des explications sur un problème afin de mieux comprendre son origine.
En tant qu'agent, je souhaite générer plusieurs propositions de réponse afin de choisir la plus adaptée.
En tant qu'agent, je souhaite conserver l'historique de mes conversations avec l'IA afin de retrouver les échanges précédents.
•      En tant que client, je souhaite discuter avec un assistant IA afin d'obtenir rapidement une réponse à une question fréquente.
•      En tant que client, je souhaite recevoir une réponse basée uniquement sur les articles validés de la base de connaissances.
•      En tant que client, je souhaite être invité à créer un ticket lorsqu'aucune réponse fiable n'est disponible.
•      En tant que client, je souhaite conserver l'historique de mes conversations avec l'assistant IA.

BONUS :
Notifications:

•      En tant qu'agent, je souhaite être notifié lorsqu'un ticket m'est affecté ou qu'un client répond.
•      En tant que client, je souhaite être notifié lorsqu'un agent répond à mon ticket.


6. Règle de gestion
•      Toute demande client passe d'abord par une recherche dans la base de connaissances ,un ticket n'est créé que si aucun article ne correspond.
•      La recherche dans la base de connaissances repose sur une correspondance de mots-clés (index full-text) elle ne génère aucun texte.
•      Chaque tentative de recherche est journalisée (requête, article trouvé le cas échéant, résultat), qu'elle aboutisse ou non à un ticket.
•      Un ticket appartient à un seul client et peut être affecté à un seul agent à la fois.
•      Le statut d'un ticket suit une progression : ouvert → en cours → résolu → fermé.
•      Seul un administrateur peut créer, modifier ou supprimer une catégorie, un article ou un compte.
•      Tout appel à l'IA s'exécute en tâche de fond (Job/Queue) ; il ne bloque jamais une requête HTTP.
•      Une suggestion de l'IA n'est jamais envoyée au client sans validation explicite de l'agent.
Les réponses fondées sur un article publié et validé de la base de connaissances peuvent être envoyées automatiquement au client. 
•      Chaque réponse et chaque changement de statut sont historisés avec leur auteur et leur date.









7. Technologies utilisées 
Couche
Technologie
Backend
Laravel 13, PHP 8.4
Base de données
MySQL
Frontend
Blade, Tailwind CSS
Authentification
Laravel Sanctum
Intelligence Artificielle
Laravel AI + API Groq
Traitement asynchrone
Laravel Queues & Jobs
Tests
Pest
Conteneurisation
Docker
Intégration continue
GitHub Actions
Documentation API
Scribe
Gestion de projet
Jira
Versionnement
Git & GitHub 
