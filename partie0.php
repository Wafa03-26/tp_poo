<?php
class Voiture {

    public $marque;
    public $couleur;

    public function setMarque($m) {
        $this->marque = $m;
    }

    public function setCouleur($c) {
        $this->couleur = $c;
    }

    public function afficher() {
        echo "<br> Marque : " . $this->marque . 
             " || Couleur : " . $this->couleur . PHP_EOL;
    } // $this = l'objet qui appelle la méthode

}

$v1 = new Voiture();
$v1->setMarque("Toyota");
$v1->setCouleur("Rouge");
$v1->afficher();

$v2 = new Voiture();
$v2->setMarque("Renault");
$v2->setCouleur("Noir");
$v2->afficher();

?>