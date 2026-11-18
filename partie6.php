<?php


echo "=== 6.1 DÉCOUVERTE DE L'HÉRITAGE ===" . PHP_EOL;

class Animal {
    public function manger(): void {
        echo "Je mange." . PHP_EOL;
    }
}

class Chien extends Animal {
    public function aboyer(): void {
        echo "Wouf !" . PHP_EOL;
    }
}

// 6.1.a : Ajout de la classe Chat (fille de Animal, sœur de Chien)
class Chat extends Animal {
    public function miauler(): void {
        echo "Miaou !" . PHP_EOL;
    }
}

$c = new Chien();
echo "<br>";
$c->manger();    // Héritée d'Animal
echo "<br>";
$c->aboyer();    // Propre à Chien
(new Chat())->miauler(); // Propre à Chat

try {
    // 6.1.b : $c->miauler(); 
    // Erreur : Un Chien ne possède pas la méthode miauler() (classes sœurs indépendantes).
    // Décommenter la ligne suivante pour tester l'erreur :
    $c->miauler(); 
} catch (Error $e) {
    echo "Erreur 6.1.b : " . $e->getMessage() . PHP_EOL;
}

echo "<br><hr>" . PHP_EOL;


// =========================================================================
// 6.2 parent:: ET REDÉFINITION DE MÉTHODE
// =========================================================================

echo "=== 6.2 parent:: ET REDÉFINITION ===" . PHP_EOL;

class PersonneSimple {
    public function __construct(protected string $nom) {}

    public function presentation(): string {
        return "Je suis " . $this->nom;
    }
}

class EtudiantSimple extends PersonneSimple {
    private string $filiere;

    public function __construct(string $nom, string $filiere) {
        // Appelle explicitement le constructeur de la classe mère
        parent::__construct($nom);
        $this->filiere = $filiere;
    }

    public function presentation(): string {
        // Réutilise la méthode du parent et la complète
        return parent::presentation() . ", étudiant en " . $this->filiere;
    }
}

$eSimple = new EtudiantSimple("Ahmed", "Informatique");
echo "<br>";
echo $eSimple->presentation() . PHP_EOL; // Je suis Ahmed, étudiant en Informatique
echo "<br>";

// 6.2.a : Démonstration de l'oubli de parent::__construct()
class EtudiantOubli extends PersonneSimple {
    private string $filiere;

    public function __construct(string $nom, string $filiere) {
        // parent::__construct($nom); <- Oublié volontairement !
        $this->filiere = $filiere;
    }
}

try {
    $eOubli = new EtudiantOubli("Ahmed", "Informatique");
    echo $eOubli->presentation() . PHP_EOL; 
} catch (Error $err) {
    echo "Erreur 6.2.a (Propriété non initialisée) : " . $err->getMessage() . PHP_EOL;
}
// Explication 6.2.b : $nom est en 'protected' pour être accessible par l'enfant (EtudiantSimple), 
// tout en restant protégé et invisible du monde extérieur.

echo "<br><hr>" . PHP_EOL;


// =========================================================================
// 6.3 final ET readonly
// =========================================================================

echo "=== 6.3 final ET readonly ===" . PHP_EOL;

class Base {
    final public function afficher(): void {
        echo "Méthode finale (ne peut pas être redéfinie)" . PHP_EOL;
    }
}

final class Outil {
    public const VERSION = "1.0";
}

class Produit {
    public string $reference;

    public function __construct(string $reference) {
        $this->reference = $reference; // readonly peut être utilisé si PHP >= 8.1
    }
}
echo "<br>";
(new Base())->afficher();
echo "<br>";
echo "Outil version : " . Outil::VERSION . PHP_EOL;
echo "<br>";
$prod = new Produit("REF-001");
echo "Référence du produit : " . $prod->reference . PHP_EOL;

/
echo "<br><hr>" . PHP_EOL;


// =========================================================================
// 6.4 EXERCICE PRINCIPAL : Etudiant extends Personne
// =========================================================================

echo "=== 6.4 EXERCICE PRINCIPAL (Etudiant extends Personne) ===" . PHP_EOL;
echo "<br>";
class Personne {
    public string $nom;
    public string $prenom;
    private string $dateNaissance;
    private string $villeNaissance;
    private string $cin;

    public function __construct(
        string $nom,
        string $prenom,
        string $dateNaissance,
        string $villeNaissance,
        string $cin
    ) {
        $this->nom            = $nom;
        $this->prenom         = $prenom;
        $this->dateNaissance  = $dateNaissance;
        $this->villeNaissance = $villeNaissance;
        $this->cin            = $cin;
    }

    // 6.4.a : Doit être 'protected' (et non 'private') pour être accessible par la classe Etudiant
    protected function getAge(): int {
        $naissance = new DateTime($this->dateNaissance);
        $aujourdhui = new DateTime();
        return $naissance->diff($aujourdhui)->y;
    }

    public function __toString(): string {
        return "{$this->prenom} {$this->nom} ({$this->getAge()} ans)";
    }
}

class Etudiant extends Personne {
    private string $cne;

    public function __construct(
        string $nom,
        string $prenom,
        string $dateNaissance,
        string $villeNaissance,
        string $cin,
        string $cne
    ) {
        // Initialisation des attributs de la classe mère
        parent::__construct($nom, $prenom, $dateNaissance, $villeNaissance, $cin);
        $this->cne = $cne;
    }

    // 2. Assesseur getCne()
    public function getCne(): string {
        return $this->cne;
    }

    // 3. Redéfinition de __toString() (Principe DRY avec parent::__toString())
    public function __toString(): string {
        return parent::__toString() . " - CNE : " . $this->cne;
    }

    // Méthode métier supplémentaire utilisant getAge() du parent
    public function ageDansDeuxAns(): int {
        return $this->getAge() + 2; 
    }
}

// 4. Déclaration du tableau de 5 étudiants et affichage via foreach
$etudiants = [
    new Etudiant("Alaoui", "Sara", "2004-05-14", "Marrakech", "EE123456", "R130000001"),
    new Etudiant("Benali", "Youssef", "2003-11-02", "Casablanca", "BK654321", "R130000002"),
    new Etudiant("Idrissi", "Omar", "2004-01-27", "Fès", "CD112233", "R130000003"),
    new Etudiant("Tazi", "Salma", "2003-08-19", "Rabat", "A998877", "R130000004"),
    new Etudiant("Chraibi", "Hamza", "2004-03-30", "Béni Mellal", "T445566", "R130000005")
];

foreach ($etudiants as $e) {
    echo $e . PHP_EOL;
    echo "<br>";
}

echo "<br>";
echo "Test méthode personnalisée : Dans deux ans, " . $etudiants[0]->prenom . " aura " . $etudiants[0]->ageDansDeuxAns() . " ans." . PHP_EOL;

