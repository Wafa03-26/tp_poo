<?php
class Personne {

    // ----- Attributs publics -----
    public string $nom;
    public string $prenom;

    // ----- Attributs privés -----
    private string $dateNaissance;   // format "AAAA-MM-JJ"
    private string $villeNaissance;
    private string $cin;

    // ----- Constructeur -----
    public function __construct(
        string $nom,
        string $prenom,
        string $dateNaissance,
        string $villeNaissance,
        string $cin
    ) {
        // TODO : initialiser les 5 attributs avec $this->
         
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->dateNaissance = $dateNaissance;
        $this->villeNaissance = $villeNaissance;
        $this->cin = $cin;

    } 

    
    
    // ----- Méthode publique -----
    public function getName(): string {
        // TODO : retourner "Prénom Nom"
        return $this->prenom . " " . $this->nom;
    }
    

    // ----- Méthode privée -----
    private function getAge(): int {
        // TODO : calculer l'âge avec DateTime
        $naissance = new DateTime($this->dateNaissance);
        $aujourdhui = new DateTime();
        return $naissance->diff($aujourdhui)->y;
    }


}
$p = new Personne(
    "Hachimi", 
    "Sara", 
    "2000-05-14", 
    "Marrakech", 
    "EE123456");

echo $p->getName() . PHP_EOL;   // Sara Hachimi  : OK (méthode publique)
echo '<br/>';
echo $p->nom . PHP_EOL;         // Hachimi       : OK (attribut public)
echo '<br/>';
//  Expérimentation : chaque accès illégal lève une Error qu'on peut attraper
$tests = [
    'echo $p->cin'           => fn() => $p->cin,
    'echo $p->dateNaissance' => fn() => $p->dateNaissance,
    'echo $p->getAge()'      => fn() => $p->getAge(),
];

foreach ($tests as $instruction => $test) {
    try {
        $test();
    } catch (Error $e) {
        echo "[$instruction] => " . $e->getMessage() . PHP_EOL;
    }
}

/*
 * 1.1 : cin et dateNaissance sont private -> "Cannot access private property".
 *       getAge() est private -> "Call to private method ... from global scope".
 * 1.4 : avec protected, l'accès depuis l'extérieur reste interdit ;
 *       la différence ne concerne que les classes filles (voir Partie 6).
 */
?>