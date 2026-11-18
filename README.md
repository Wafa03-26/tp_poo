# Corrigé – Réponses aux questions du TP POO en PHP

> Les réponses ont été vérifiées en exécutant les scripts `partie0.php` à `partie7.php` avec **PHP 8.3**.ABS
> Les messages d'erreur cités sont ceux produits réellement par PHP 8.x.

---

## Partie 0 – Échauffement

**0.3  `$v1` et `$v2` partagent-ils la même valeur de `$marque` ?**
Non. Chaque objet possède **sa propre copie** des propriétés de la classe. Modifier `$v2->marque` ne change pas `$v1->marque`. La classe est le plan, les objets sont des exemplaires indépendants (leur *état* est propre à chacun).

---

## Partie 1 – Visibilité et encapsulation

**1.1 Que remarquez-vous ?**

| Instruction | Résultat |
|---|---|
| `echo $p->cin;` | `Error : Cannot access private property Personne::$cin` |
| `echo $p->dateNaissance;` | `Error : Cannot access private property Personne::$dateNaissance` |
| `echo $p->getAge();` | `Error : Call to private method Personne::getAge() from global scope` |

Les membres `private` sont **inaccessibles depuis l'extérieur** de la classe, alors que `nom`, `prenom` et `getName()` (publics) fonctionnent.

**1.2  Pourquoi laisser `cin` en `public` serait dangereux ?**
N'importe quel code pourrait **lire** cette donnée personnelle sensible et surtout la **modifier** sans aucun contrôle (valeur invalide, usurpation…). L'encapsulation permet de contrôler l'accès (lecture seule, validation) via des méthodes.

**1.3  `getAge()` est privée : est-elle inutile ?**
Non. Une méthode privée est un **outil interne** de la classe : elle est appelée par d'autres méthodes de la même classe (par exemple `__toString()` en Partie 4). Elle factorise du code sans l'exposer à l'extérieur.

**1.4 Passer `cin` en `protected`**
Depuis l'extérieur, rien ne change : l'accès reste interdit. La différence n'apparaît qu'avec l'**héritage** : une classe fille peut lire un attribut `protected`, pas un attribut `private` (voir Partie 6).

---

## Partie 2 – Constructeurs et destructeur

**2.1.a Que se passe-t-il avec `new Voiture("Noire")` et `new Voiture(120)` ?**

- `new Voiture("Noire")` → couleur `Noire`, vitesse `0` (le deuxième paramètre prend sa valeur par défaut).
- `new Voiture(120)` → **aucune erreur** : sans `declare(strict_types=1)`, PHP convertit l'entier `120` en chaîne `"120"` → couleur = `"120"`. C'est un piège classique.
- Avec `declare(strict_types=1);` en première ligne du fichier, cet appel provoquerait une `TypeError`.
- `new Voiture("Rouge", "abc")` → `TypeError : Argument #2 ($vitesse) must be of type int, string given`.

**2.1.b  Avantage de la promotion de propriétés**
On **déclare et on initialise** les attributs directement dans la signature du constructeur : moins de code répétitif (plus besoin de déclarer l'attribut puis d'écrire `$this->x = $x;`), donc moins de risques d'oubli ou d'incohérence.

**2.2.a  Deux constructeurs : que remarquez-vous ?**
`Fatal error: Cannot redeclare Personne::__construct()`. PHP **ne supporte pas la surcharge** de méthodes par signature (nombre/type d'arguments) comme Java ou C++ : un nom de méthode = une seule définition.

**2.2.b Solution avec un seul constructeur**
Utiliser des **valeurs par défaut** :

```php
public function __construct(public string $nom = "", public string $prenom = "") {
    echo "Création " . __CLASS__ . PHP_EOL;
}
```

`new Personne()` et `new Personne("Alaoui", "Sara")` fonctionnent tous les deux.

**2.2.c (bonus) Méthode statique de fabrique**

```php
public static function depuisTableau(array $data): self {
    return new self($data['nom'] ?? "", $data['prenom'] ?? "");
}
// Personne::depuisTableau(['nom' => 'Idrissi', 'prenom' => 'Omar']);
```

**2.3.a  Ordre des messages (avec `unset`)**
1. `Voiture Blanche créée.`
2. `Voiture Blanche détruite.` (déclenchée par `unset`)
3. `Fin du script.`

**2.3.b Sans `unset`**
1. `créée` → 2. `Fin du script.` → 3. `détruite` : l'objet est détruit **à la fin du script**.

**2.3.c Deux objets sans `unset`**
Ils sont détruits à l'arrêt du script, après le dernier `echo`. Avec PHP 8.3, nous avons observé l'**ordre inverse de la création** (B puis A), mais le langage ne le garantit pas : il ne faut jamais en dépendre.

**2.3.d Destructeur dans `Personne`**

```php
public function __destruct() {
    echo "Détruire " . __CLASS__ . PHP_EOL;
}
```

---

## Partie 3 – Surcharge de propriétés

**3.1 Les quatre méthodes** : voir `partie3.php`. Résultats attendus :

```
Alaoui          // __get
bool(true)      // __isset
bool(false)     // après __unset
Propriété telephone inexistante   // __set sur propriété inexistante
```

**3.2  Sans `__set`, que se passe-t-il pour `$p->nom = "X"` si `nom` est privé ? Et avec `__set` ?**

- **Sans `__set`** : `Error : Cannot access private property Personne::$nom`.
- **Avec `__set`** : PHP **n'affiche pas d'erreur**, il appelle `__set("nom", "X")` à la place, et c'est à nous de décider quoi faire.

> Remarque : si la propriété n'est ni privée ni protégée mais simplement **inexistante** et qu'aucun `__set` n'existe, PHP crée une propriété dynamique (dépréciée depuis PHP 8.2).

**3.3  `__set` peut-il casser l'encapsulation ?**
Oui, si on écrit `$this->$name = $value;` sans contrôle : n'importe quel attribut devient modifiable de l'extérieur, comme s'il était public. Il faut :

- une **liste blanche** de propriétés autorisées (`property_exists` ou tableau explicite) ;
- une **validation** de la valeur (type, longueur, format) ;
- éventuellement déléguer à des *setters* spécifiques.

**3.4 Validation du nom** : voir `partie3.php` (`strlen($value) < 2` → `InvalidArgumentException`).

---

## Partie 4 – `__toString`

**4.1  `getAge()` est privée, pourtant elle fonctionne dans `__toString()`. Pourquoi ?**
`__toString()` est une méthode **de la classe `Personne`** : le code interne à une classe peut appeler toutes ses méthodes, y compris privées. La visibilité `private` interdit l'accès depuis l'**extérieur**, pas depuis l'intérieur.

**4.2 Que se passe-t-il si `__toString()` retourne un entier ?**

- **Sans `strict_types`** : l'entier est converti en chaîne (`42` → `"42"`), pas d'erreur.
- **Avec `declare(strict_types=1)`** ou en retournant un `array` / un objet : `TypeError : Return value must be of type string, array returned`.

**4.3 (bonus) Intérêt de `__toString()` par rapport à `getName()`**

- Conversion **automatique** : `echo $p;`, `"Bonjour " . $p`, `strtoupper($p)`, journaux (logs), messages d'exceptions…
- Convention **standard** du langage : tout code qui attend une chaîne (`Stringable`) peut recevoir l'objet, sans connaître le nom d'une méthode particulière. PHP 8 ajoute automatiquement l'interface `Stringable` aux classes qui définissent `__toString()`.

---

## Partie 5 – Sérialisation

**5.1 Le mot de passe figure-t-il dans la chaîne sérialisée ?**
Non :

```
O:11:"Utilisateur":2:{s:3:"nom";s:5:"Alice";s:3:"age";i:25;}
```

**5.2  Sans `__serialize()`**
La propriété privée est sérialisée avec un nom préfixé par des octets nuls (`\0Classe\0motDePasse`), et **le mot de passe apparaît en clair** :

```
O:15:"UtilisateurNaif":2:{s:3:"nom";s:5:"Alice";s:27:"*UtilisateurNaif*motDePasse";s:9:"secret123";}
```

(les `*` remplacent ici les octets nuls pour l'affichage). Si cette chaîne est stockée (fichier, session, cookie, journal), le secret **fuit**.

**5.3  Valeur de `$motDePasse` après `unserialize`**
Chaîne vide `""` (initialisée dans `__unserialize()`). Dans une vraie application :

- on ne stocke **jamais** un mot de passe en clair, seulement son **hash** (`password_hash`) ;
- après désérialisation, on demande à l'utilisateur de se **ré-authentifier** plutôt que de restaurer un secret.

**5.4  Pourquoi ne jamais appeler `unserialize()` sur des données utilisateur ?**
Un attaquant peut fabriquer une chaîne qui fait **instancier des classes de l'application** (même sans rapport avec l'usage prévu) et déclenche automatiquement leurs méthodes magiques (`__wakeup`, `__destruct`, `__toString`…). En enchaînant plusieurs classes (*gadget chain*), il peut aboutir à la suppression de fichiers, une injection SQL ou une exécution de code : c'est la **PHP Object Injection**.

Alternatives :

- `json_encode()` / `json_decode()` : n'instancient aucun objet ;
- `unserialize($s, ['allowed_classes' => false])` si on ne peut pas faire autrement ;
- **signer** les données (HMAC) et vérifier la signature avant tout traitement.

---

## Partie 6 – Héritage

**6.1.b  Peut-on appeler `$c->miauler()` sur un `Chien` ?**
Non : `Error : Call to undefined method Chien::miauler()`. `Chien` et `Chat` héritent toutes deux d'`Animal`, mais sont des classes **sœurs** : `miauler()` n'existe que dans `Chat`.

**6.2.a  Sans `parent::__construct($nom)`**
`Error : Typed property PersonneSimple::$nom must not be accessed before initialization`. Quand la classe fille définit **son propre** constructeur, celui du parent **n'est pas appelé automatiquement** : c'est à nous d'écrire `parent::__construct(...)`.

**6.2.b  Pourquoi `$nom` est `protected` et non `private` ?**
Pour que la classe fille (`Etudiant`) puisse y accéder (`$this->nom`). En `private`, seule la classe qui le déclare y a accès.

**6.3.a  Les trois erreurs**

| Tentative | Erreur |
|---|---|
| Redéfinir `afficher()` dans `Fille extends Base` | `Fatal error: Cannot override final method Base::afficher()` |
| `class SousOutil extends Outil {}` | `Fatal error: Class SousOutil cannot extend final class Outil` |
| `$p->reference = "REF-002";` | `Error : Cannot modify readonly property Produit::$reference` |

Les deux premières sont des erreurs **fatales à la compilation** (impossibles à attraper avec `try/catch`), la troisième est une `Error` attrapable.

**6.3.b  Exemples réels**

- **`readonly`** : identifiant, référence produit, date de création, montant d'une transaction passée → une valeur qui ne doit plus changer après la création.
- **`final`** : méthode critique pour la sécurité (vérification de signature, hachage), classe utilitaire ou de configuration qu'on ne souhaite pas voir détournée par héritage.

**6.4.a  Appeler `getAge()` (privée) depuis `Etudiant`**
`Error : Call to private method Personne::getAge() from scope Etudiant`. Il faut passer `getAge()` en **`protected`**.

**6.4.b  Accéder à `$this->dateNaissance` depuis `Etudiant`**
Impossible : un membre `private` n'est visible que dans la classe qui le déclare. `Etudiant` *est une* `Personne` (relation « est-un »), mais l'encapsulation protège les détails internes **même vis-à-vis des classes filles**. Pour y accéder : un *getter* `public`/`protected`, ou déclarer l'attribut `protected`.

**6.4.c  Avantage de `parent::__toString()`**
Principe **DRY** (*Don't Repeat Yourself*) : on réutilise le code du parent et on le complète. Si `Personne::__toString()` change (nouveau format), `Etudiant` en bénéficie automatiquement, sans modification.

---

## Partie 7 – Compte bancaire

**7.1  Erreur de syntaxe**
Dans `($this->montant 1*$this->interet / 100)`, il **manque un opérateur** entre `$this->montant` et `1` : `Parse error: syntax error, unexpected integer "1"`.

**7.2  Formule mathématique**
Avec un taux de `t` %, le montant après un an est :

```
nouveau_montant = montant × (1 + t / 100)
```

La formule d'origine multipliait le montant par `(montant + …)`, ce qui élève le solde au carré, donc complètement faux.

```php
$this->montant = $this->montant * (1 + $this->interet / 100);
```

**7.3  Ordre des paramètres du constructeur**
Le code d'origine prend `($interet, $montantInitial)` alors que l'énoncé parle d'« un **montant initial** et un **intérêt** ». Comme les deux paramètres sont numériques, une inversion (`new Compte(400, 10)` au lieu de `new Compte(10, 400)`) **ne déclenche aucune erreur** mais produit un résultat faux : un bug silencieux. Remèdes : ordre cohérent avec l'énoncé, types explicites, **arguments nommés** (`new Compte(montantInitial: 400, interet: 10)`).

**7.4 Code corrigé et résultats** : voir `partie7.php`.

| Compte | Montant initial | Taux | Après 10 ans |
|---|---|---|---|
| `$c1` | 400 DH | 10 % | **1 037,50 DH** |
| `$c2` | 2 500 DH | 5 % | **4 072,24 DH** |

**7.5 `deposer()` et `estValide()`** : `deposer()` est publique et appelle `estValide()` (privée). Appeler `$c->estValide(100)` de l'extérieur provoque `Call to private method Compte::estValide() from global scope`.

**7.6 `CompteEpargne`** : l'attribut `$montant` doit être **`protected`** dans `Compte` pour que `CompteEpargne::afficherSolde()` puisse le lire.

**7.7 `retirer()`** : lance une `RuntimeException("Solde insuffisant.")` si le montant demandé dépasse le solde, et une `InvalidArgumentException` si le montant est ≤ 0.

---

## Questions de synthèse

**1. Classe vs objet ?**
Une **classe** est un modèle (plan) qui décrit des propriétés et des méthodes. Un **objet** est une **instance** concrète de cette classe, créée avec `new`, avec ses propres valeurs.

**2. Trois avantages de la POO ?** (au choix)

- Meilleure **organisation** du projet ;
- **Maintenance** et évolution facilitées ;
- **Réutilisation** du code grâce à l'héritage (moins de duplication) ;
- **Encapsulation** : masquage des détails d'implémentation ;
- **Agrégation** : objets composés d'autres objets.

**3. Pourquoi `private` / `protected` pour les attributs ?**
Pour appliquer l'**encapsulation** : on contrôle comment les données sont lues/modifiées (validation, cohérence, sécurité) et on peut changer l'implémentation interne sans casser le code qui utilise la classe.

**4. `private` vs `protected` avec l'héritage ?**
`private` : accessible **uniquement** dans la classe qui le déclare. `protected` : accessible dans la classe **et** dans ses classes filles.

**5. Une méthode magique doit-elle être `public` ?**
Oui : sinon PHP émet un `E_WARNING`. **Exceptions tolérées** : `__construct()`, `__destruct()` et `__clone()` (par exemple pour interdire l'instanciation ou le clonage).

---

##  Conseils pour l'évaluation

- Vérifier que les étudiants **reproduisent les erreurs** () et savent les expliquer, plus que la simple obtention du résultat.
- Les pièges les plus formateurs : constructeur du parent non appelé (6.2.a), `getAge()` privée dans l'héritage (6.4.a), formule du compte (7.2), inversion silencieuse des paramètres (7.3), `unserialize()` non sécurisé (5.4).
- Barème indicatif : exécution correcte des scripts (40 %), réponses aux questions  (30 %), qualité du code (encapsulation, types, conventions) (20 %), exercices bonus (10 %).
