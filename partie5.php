<?php
/**
 * CORRIGÉ – Partie 5 : Sérialisation et Désérialisation sécurisée (__serialize et __unserialize)
 * Exécution en CLI : php partie5.php
 */

class Utilisateur {
    public string $nom;
    public int $age;
    private string $motDePasse;

    public function __construct(string $nom, int $age, string $motDePasse) {
        $this->nom        = $nom;
        $this->age        = $age;
        $this->motDePasse = $motDePasse;
    }

    public function __serialize(): array {
        return [
            'nom' => $this->nom,
            'age' => $this->age,
            // $motDePasse est volontairement exclu pour des raisons de sécurité
        ];
    }

    public function __unserialize(array $data): void {
        $this->nom        = $data['nom'];
        $this->age        = $data['age'];
        $this->motDePasse = "";   // à réinitialiser : il n'a jamais été sérialisé
    }
}

// ==========================================
// 1. TESTS DE SÉRIALISATION SÉCURISÉE
// ==========================================

$u = new Utilisateur("Alice", 25, "secret123");
$serialise = serialize($u);
echo "Sérialisé : $serialise" . PHP_EOL;    // 5.1 : pas de mot de passe dans la chaîne
echo "<br>";

$restaure = unserialize($serialise);
echo "Objet restauré (var_dump) : <pre>";
var_dump($restaure);                      // 5.3 : $motDePasse vaut "" (chaîne vide)
echo "</pre><hr>";


// ==========================================
// 2. DÉMONSTRATION DU RISQUE SANS __serialize (5.2)
// ==========================================

class UtilisateurNaif {
    public function __construct(
        public string $nom,
        private string $motDePasse
    ) {}
}

$brut = serialize(new UtilisateurNaif("Alice", "secret123"));

// Les propriétés privées sont préfixées par "\0NomClasse\0" (octets nuls) -> on les remplace par "*" pour les afficher
echo "Sans __serialize : " . str_replace("\0", "*", $brut) . PHP_EOL;
echo "<br>";
// Résultat visuel : on voit clairement "secret123" en clair ! 
// Risque majeur de fuite si la chaîne est stockée (fichier de log, session non chiffrée, cookie...).
echo "<hr>";


// ==========================================
// 3. ALTERNATIVES SÛRES ET PROTECTION (5.4)
// ==========================================

echo "--- Alternatives sûres ---" . PHP_EOL;
echo "<br>";

// Alternative JSON : convertit en texte brut sans instancier d'objets PHP
$json = json_encode(['nom' => $u->nom, 'age' => $u->age]);
echo "JSON : $json" . PHP_EOL;
echo "<br>";

$data = json_decode($json, true);           // simple tableau associatif : aucun objet exécutable
var_dump($data);
echo "<hr>";

// Si l'utilisation d'unserialize() est inévitable sur des données externes : 
// On bloque l'instanciation arbitraire de classes pour empêcher la faille d'injection.
$sur = unserialize($serialise, ['allowed_classes' => false]);
echo "Avec allowed_classes=false : " . get_class($sur) . PHP_EOL;   // Affiche : __PHP_Incomplete_Class


