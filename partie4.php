<?php

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

    // Remarque : La méthode getName() a été supprimée et remplacée par __toString()

    /**
     * Méthode privée : outil interne pour calculer l'âge dynamiquement.
     */
    private function getAge(): int {
        return (new DateTime($this->dateNaissance))->diff(new DateTime())->y;
    }

    /**
     * Méthode magique __toString() : définit le comportement de l'objet 
     * lorsqu'il est manipulé ou affiché comme une chaîne de caractères.
     */
    public function __toString(): string {
        return $this->prenom . " " . $this->nom . " (" . $this->getAge() . " ans)";
    }
}

// ==========================================
// 2. TESTS ET CAS PRATIQUES
// ==========================================

$p = new Personne("Ala", "Sara", "2000-05-14", "Marrakech", "EE123456");

echo "<h3>1. Affichage direct de l'objet :</h3>";
echo $p . PHP_EOL; // Conversion automatique grâce à __toString()
echo '<br /><hr />';

// --- Question 4.3 (Bonus) : Situations pratiques d'utilisation ---
echo "<h3>2. Situations pratiques (__toString vs getName) :</h3>";

// A. Concaténation de chaînes
echo "Bonjour " . $p . PHP_EOL; 
echo '<br />';

// B. Manipulation avec des fonctions natives de chaînes (ex: strtoupper)
echo "En majuscules : " . strtoupper($p) . PHP_EOL; 
echo '<br />';

// C. Forcer le cast explicite en chaîne
$ageActuel = (new DateTime("2000-05-14"))->diff(new DateTime())->y;
$attendu = "Sara Ala (" . $ageActuel . " ans)";

echo (string) $p === $attendu
    ? "Cast explicite (string)\$p : OK" . PHP_EOL
    : " Différent" . PHP_EOL;
echo '<br />';

// D. Vérification de l'interface Stringable (native depuis PHP 8)
var_dump($p instanceof Stringable); // true (implémenté automatiquement en PHP 8+)
echo '<br /><hr />';


// --- Question 4.2 : Que se passe-t-il si __toString() ne retourne pas une chaîne ? ---

echo "<h3>3. Test des erreurs de type de retour (4.2) :</h3>";

// Cas A : Retour d'un entier (int)
class MauvaisEntier {
    public function __toString(): string {
        return 42; // Retourne un entier au lieu d'une chaîne
    }
}
echo "Tentative avec retour int (converti automatiquement en mode faible) : ";
echo new MauvaisEntier() . PHP_EOL; 
echo '<br />';

// Cas B : Retour d'un tableau (array) -> Provoque un TypeError
class MauvaisTableau {
    public function __toString(): string {
        return []; // Retourne un tableau
    }
}

try {
    echo new MauvaisTableau();
} catch (TypeError $e) {
    echo " Capture de l'erreur TypeError : " . $e->getMessage() . PHP_EOL;
}