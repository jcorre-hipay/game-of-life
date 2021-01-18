# Jeu de la vie

Bienvenue à toi qui vient visiter ce modeste dépôt.

Tu trouveras ici une application de démonstration, basée sur le célèbre
[Jeu de la vie](https://fr.wikipedia.org/wiki/Jeu_de_la_vie) de John Horton Conway,
illustrant une architecture logicelle compatible avec l'approche *Domain Driven Design*.

Elle est écrite en *PHP 7.3*, utilise le framework *Symfony 5.2* et est déployable avec
*Docker*. Les tests sont réalisés avec *Behat*, *PhpSpec* et *PhpUnit*.

## :rocket: Démarrage rapide

L'application renferme un artefact magique que l'on nomme le `Makefile`, dont tu pourras
te servir pour la faire fonctionner. Pour commencer, utilise le parchemin suivant afin
d'assembler l'image Docker adéquat et d'invoquer un conteneur :

```bash
make build
make up
```

Ensuite, il est d'usage de se présenter convenablement au conteneur, sans quoi il risquerait
de t'ignorer. Cela se fait au travers de la cérémonie des tests applicatifs :

```bash
make test
```

Enfin, si tout se passe bien, tu accéderas à l'application à l'adresse
[http://localhost:3000](http://localhost:3000). Une fois que tu auras terminé, n'oublie pas de
libérer le conteneur à l'aide de l'invocation suivante :

```bash
make down
```

Si tu veux faire un tour des fonctionnalités, je t'invite à prendre connaissance des écrits
conservés dans le répertoire `features`. Ils relatent les différents cas d'utilisation de
l'application.

## :triangular_ruler: Architecture

### :wrench: Architecture hexagonale et techno-agnostisme

Le cœur de l'architecture repose sur la parole divine suivante :

> Ne mélange pas métier et technique !

Les technologies évoluent : les frameworks d'hier tombent en désuétude, de nouvelles bibliothèques
font leurs apparitions... Le métier, quant à lui, consistitue la réelle valeur de l'application.
Coupler les deux, c'est prendre le risque de voir la valeur métier dépérir avec des technologies
veillissantes ou la compromettre suite à des évolutions techniques non maîtrisées. Les légendes
de projets fantômes qui ont suivis l'une ou l'autre de ces voies sont désormais légions.

Pour ne pas tomber dans cet écueil, cette application est divisée en trois contrées bien
distinctes : le Domaine, l'Application et l'Infrastructure.

![Carte globale](./doc/overview.png)
    
Le **Domaine** regroupe toute la logique métier de l'application. Les classes que tu trouveras
dans cette contrée sont totalement indépendantes de tout framework ou biblothèque. D'ailleurs,
elles n'ont même aucune connaissance des deux autres contrées. Tu peux t'en assurer en allant
voir les `use`, ces petits esprits nichant au dessus des classes : ils réfèrent tous à des classes
du Domaine et de nulle part ailleurs.

L'**Application** regroupe les services répondant aux cas d'utilisation de l'application. Ils
servent d'intermédiaires entre le Domaine et ceux qui veulent communiquer avec, comme par exemple
la guilde des *contrôleurs*. Il est bon de savoir qu'ils ont instaurés un contrôle strict des
frontières : toute requête, réponse, ou même exception est issue de l'Application et non du
Domaine, même lorsqu'il s'agit d'une simple transcription d'une entité.
Seule exception à cela : les *événements* du Domaine sont librement accessibles à tous.

L'**Infrastructure** est une vaste région dans laquelle tu pourras croiser toute sorte de
classes techniques : des contrôleurs, des adaptateurs de bibliothèques externes, des implémentations
génériques, etc. Bien qu'elles soient dépourvues de connaissance métier, elles sont autant de petits
rouages qui permettent à l'application de tourner.

Si tu t'aventures à la lisière du Domaine, tu remarqueras sûrement des interfaces n'ayant aucune
implémentation à l'intérieur du royaume. Pour les trouver, il te faudra traverser la frontière car
celles-ci logent au sein de l'Infrastructure. C'est ainsi que les composants techniques nécessaire
au Domaine y sont injectés. À titre d'exemple, tu pourras rechercher l'interface
`ColonyRepositoryInterface` et son implémentation `SqliteColonyRepository`.

### :scissors: Séparation des services de lecture et d'écriture

L'Application est divisée en deux territoires aux caractères bien différents.

Si ta quête te mène dans les monts **Query**, tu y renconteras des services dédiés à
l'observation du Domaine. Ceux-ci ont fait vœux de ne jamais le modifier, donc toute requête
que tu leur soumettras te donnera une vue instantanée de son état sans risque de l'influer.
C'est d'ailleurs pour cette raison que les réponses qu'ils fournissent sont forgées de manière
à ne jamais donner d'accès direct aux objets du Domaine.

À l'inverse, si tu passes par les monts **Command**, tu feras la connaissance des services
dédiés à l'évolution du Domaine. Pour chaque action qu'ils mènent, ils mettent à disposition
la liste des *événements* résultants.

Si tu veux explorer ces territoires, il te faudra emprunter le fleuve des bus. Il possède deux
embouchures aux noms suffisamment explicites : le `QueryBusInterface` et le `CommandBusInterface`.
Rends toi dans la baie des contrôleurs et cherche le `ColonyController` pour commencer ta quête.

### :floppy_disk: Modèle de stockage événementiel

Si tu t'intéresses au stockage des données, tu seras peut-être surpris de voir que les entités
ne reposent pas dans des tables relationnelles traditionnelles où chaque ligne accueille une
entité. À la place, ce sont les *événements* du Domaine qui sont stockés. Il va de soi que cela
implique que chaque entité doit pouvoir être reconstruite à partir des événements qui lui sont
associés. Cela veut également dire que tout son historique est gardé intact et librement consultable.

Si tu désires en savoir plus, traverse la plaine des dépôts et recherche le `SqliteColonyRepository`
et sa méthode `find`.

### :lock: Immuabilité des objets

Une légende raconte qu'un sorcier itinérant, charmé par la beauté du royaume, avait pris l'habitude
d'y revenir passer quelques jours à chaque nouvel an. Lors de l'une de ses visites, il fut effrayé
de ne pouvoir reconnaître ses amis vivant là tant ils avaient changé durant l'année passée. Alors,
il lança un sort sur toute la ville qui devait figer ses habitants à tout jamais. Depuis ce jour,
tout événement suceptible de rendre un objet différent de quelque manière que ce soit ne l'affecte
pas, mais lui crée un double auquel est appliqué le changement.

Nul ne sait vraiment si cette histoire est vraie, mais si tu restes assez longtemps dans la ville
de `Colony`, tu pourras constater qu'elle est bien immuable. La méthode `evolve` ne la modifie pas
mais retourne la liste des événements qu'entrainerait l'évolution. Ces événements sont applicables
via la méthode `apply` qui retourne un nouvel objet sans toutefois modifier l'original. Ce principe
présente l'avantage de plus facilement tracer les changements d'état pour qui explore le code.

## :heavy_check_mark: Tests applicatifs

L'application est scellée par trois sceaux de test : Behat, PhpSpec et PhpUnit.

**Behat** permet de lancer les tests fonctionnels de l'application. Il s'agit d'une vision utilisateur,
de tests couvrant l'application de bout en bout, c'est à dire des contrôleurs à la base de données. Ils
permettent de vérifier que le comportement de l'application dans son ensemble est respecté.

**PhpSpec** permet à l'inverse de lancer les tests unitaires. Chaque test vérifie une classe en
isolation et s'assure que son contrat envers les autres classes est respecté.

**PhpUnit** est utilisé pour tous les autres tests : les tests des services applicatifs, les tests
des dépôts, les tests de bout en bout non fonctionnel comme une page 404, etc. Il s'agit le plus
souvent de tests techniques regroupant un ensemble logique de classes, et pouvant soliciter la
base de données.

Il existe une subtilité que tu dois connaitre si tu veux maîtriser cette puissante magie que
sont les tests applicatifs. En effet, les tests Behat et PhpUnit font appel à des agrégats de classes,
or il est parfois nécessaire de pouvoir remplacer certaines d'entre elles, faute de quoi le comportement
des tests risquerait de devenir instable : c'est par exemple le cas de la classe `GenerateUuid` qui
génére des identifiants aléatoires. Pour dompter ce genre de classe, tu devras recourir au parchemin
`services_test.yaml` afin de les substituer par des classes prédictibles.
