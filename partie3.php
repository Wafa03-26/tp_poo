<?php
class PersonneMagique {
    private string $nom = "";
    private string $prenom = "";
    private string $dateNaissance = "";

    public function __set(string $name, mixed $value): void {
        if (!property_exists($this, $name)) {
            echo "Propriété '$name' inexistante !" . PHP_EOL;
            return;
        }

        // Validation sur la propriété 'nom'
        if ($name === 'nom') {
            if (empty($value) || strlen($value) < 2) {
                throw new InvalidArgumentException("Le nom ne doit pas être vide et contenir au moins 2 caractères.");
            }
        }

        $this->$name = $value;
    }

    public function __get(string $name): mixed {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        return null;
    }

    public function __isset(string $name): bool {
        return property_exists($this, $name) && isset($this->$name) && $this->$name !== "";
    }

    public function __unset(string $name): void {
        if (property_exists($this, $name)) {
            $this->$name = "";
        }
    }
}

$p = new PersonneMagique();
$p->nom = "Hachimi";             // __set
echo $p->nom . PHP_EOL;          // __get
var_dump(isset($p->nom));        // __isset -> true
unset($p->nom);                  // __unset
var_dump(isset($p->nom));        // __isset -> false