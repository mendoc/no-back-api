<?php
/**
 * Created by PhpStorm.
 * User: Mende
 * Date: 05/06/2018
 * Time: 19:36
 */

class BDD
{
    private $config = array(
        'host' => 'mysql-mende.alwaysdata.net',
        'base' => 'mende_badol',
        'user' => 'mende',
        'pass' => 'Men5P@sS'
    );
    private $table;
    private $bdd;
    private $erreur;
    private static $instance;

    public function __construct()
    {
        try {
            $this->bdd = new PDO('mysql:host='.$this->config['host'].';dbname='.$this->config['base'], $this->config['user'], $this->config['pass'], array(PDO::ATTR_PERSISTENT => true));
        } catch (PDOException $e) {
            $this->erreur = $e->getMessage();
        }
    }

    static function get_instance()
    {
        if(is_null(self::$instance)) self::$instance = new BDD();
        return self::$instance;
    }

    public function table($table_name)
    {
        $this->table = $table_name;
        return self::$instance;
    }

    public function query($sql)
    {
        return $this->bdd->query($sql);
    }

    public function errorInfo()
    {
        return $this->bdd->errorInfo();
    }

    public function isLoaded()
    {
        return $this->bdd != null;
    }

    public function insert($data)
    {
        if (!$this->bdd) return false;
        $req  = "INSERT INTO {$this->table}";
        $req .= " (`".implode("`, `", array_keys($data))."`)";
        $req .= " VALUES ('".implode("', '", $data)."') ";
        $res = $this->bdd->query($req);
        if (!$res) {
            $this->erreur = $this->bdd->errorInfo();
            return false;
        }
        return (int)$this->bdd->lastInsertId();
    }

    public function delete($id)
    {
        if (!$this->bdd) return false;
        $sql = "DELETE FROM {$this->table} WHERE id = " . $id;
        $res =  $this->bdd->query($sql);
        if (!$res) {
            $this->erreur = $this->bdd->errorInfo();
            return false;
        }
        return true;
    }

    public function get_erreur()
    {
        if (gettype($this->erreur) == 'string') return $this->erreur;
        return $this->erreur['2'];
    }

    public function set_erreur($erreur)
    {
        $this->erreur = $erreur;
    }

    public function get($key)
    {
        if(!isset($this->config[$key])) return null;
        return $this->config[$key];
    }
}

function e($table, $data, $id = null)
{
    $bdd = BDD::get_instance();
    if (!$bdd->isLoaded()) return false;

    $sql = "SHOW FIELDS FROM " . $table;
    $colonnes_table = $bdd->query($sql);
    if (!$colonnes_table) {
        $bdd->set_erreur($bdd->errorInfo());
        return false;
    }
    if ($colonnes_table and count($colonnes_table) > 0)
    {
        $tous = $p = array();
        foreach ($colonnes_table as $colonne) $tous[] = $colonne[0];
        $champs = array_intersect(array_keys($data),$tous);
        foreach ($champs as $champ){
            $p[$champ] = $data[$champ];
        }
        if (isset($id)){
            if (count($p) == 0) {
                $bdd->set_erreur('Aucun champ n\'a été renseigné.');
                return false;
            }
            $sql = "UPDATE {$table} SET ";
            foreach ($p as $key => $valeur) $sql .= $key . " = " . "'$valeur'" . ", ";
            $sql = rtrim($sql, ', ');
            $sql .= " WHERE id = {$id};";
            $res = $bdd->query($sql);
            if (!$res) {
                $bdd->set_erreur($bdd->errorInfo());
                return false;
            }
            return true;
        }else{
            return $bdd->table($table)->insert($p);
        }
    }else{
        $bdd->set_erreur('Problème avec la table ' . $table);
        return false;
    }

}

function u($table, $data, $id = null)
{
    $bdd = BDD::get_instance();
    if (!$bdd->isLoaded()) return false;

    $sql = "SHOW FIELDS FROM " . $table;

    $colonnes_table = $bdd->query($sql);
    if (!$colonnes_table) {
        $bdd->set_erreur($bdd->errorInfo());
        return false;
    }
    if ($colonnes_table and count($colonnes_table) > 0)
    {
        $tous = $p = array();
        foreach ($colonnes_table as $colonne) $tous[] = $colonne[0];
        $champs = array_intersect(array_keys($data),$tous);
        foreach ($champs as $champ){
            $p[$champ] = $data[$champ];
        }

        if (count($p) == 0) {
            $bdd->set_erreur('Aucun champ n\'a été renseigné.');
            return false;
        }

        $sql = "UPDATE {$table} SET ";

        foreach ($p as $key => $valeur) $sql .= $key . " = " . "'$valeur'" . ", ";
        $sql = rtrim($sql, ', ');

        if ($id != null) $sql .= " WHERE id = {$id};";

        $res = $bdd->query($sql);
        if (!$res) {
            $bdd->set_erreur($bdd->errorInfo());
            return false;
        }
        return true;
    }else{
        $bdd->set_erreur('Problème avec la table ' . $table);
        return false;
    }

}
function s($table, $param1 = null, $param2 = null)
{
    $bdd = BDD::get_instance();
    if (!$bdd->isLoaded()) return false;

    if ($param1 == null){
        $res = $bdd->query("SELECT * FROM " . $table);
        if (!$res) {
            $bdd->set_erreur($bdd->errorInfo());
            return false;
        }
        return $res->fetchAll(PDO::FETCH_ASSOC);
    }else{
        switch (gettype($param1)){
            case 'integer':
                $sql = "SELECT * FROM " . $table . " WHERE id = " . $param1;
                $res = $bdd->query($sql);
                if (!$res) return false;
                $one = $res->fetch(PDO::FETCH_ASSOC);
                if (!$one) return null;
                return $one;
            case 'array':
                $sql = "SELECT * FROM " . $table;
                if (count($param1) > 0) $sql .= " WHERE ";
                $i = 0;
                foreach ($param1 as $champ => $valeur) {
                    if ($i > 0) $sql .= " AND ";
                    if (gettype($valeur) == "NULL") $sql .= $champ . " IS NULL";
                    else $sql .= $champ . " = '" . $valeur . "'";
                    $i = 1;
                }
                if ($param2 != null and gettype($param2) == 'integer'){
                    $sql .= ' AND id = ' . $param2;
                }
                $res = $bdd->query($sql);
                if (!$res) {
                    $bdd->set_erreur($bdd->errorInfo());
                    return false;
                }
                if ($param2 != null and gettype($param2) == 'integer'){
                    $one = $res->fetch(PDO::FETCH_ASSOC);
                    if (!$one) return null;
                    return $one;
                }
                return $res->fetchAll(PDO::FETCH_ASSOC);
            case 'string':
                $sql = "SELECT {$param1} FROM " . $table;
                if (count($param2) > 0) $sql .= " WHERE ";
                $i = 0;
                foreach ($param2 as $champ => $valeur) {
                    if ($i > 0) $sql .= " AND ";
                    if (gettype($valeur) == "NULL") $sql .= $champ . " IS NULL";
                    else $sql .= $champ . " = '" . $valeur . "'";
                    $i = 1;
                }
                $res = $bdd->query($sql);
                if (!$res) {
                    $bdd->set_erreur($bdd->errorInfo());
                    return false;
                }
                return $res->fetchAll(PDO::FETCH_ASSOC);
            default:
                $bdd->set_erreur('Requete inconnue');
                return false;
        }
    }
}

function x($sql){
    $bdd = BDD::get_instance();
    if (!$bdd->isLoaded()) return false;

    $res = $bdd->query($sql);
    if (!$res) {
        $bdd->set_erreur($bdd->errorInfo());
        return false;
    }
    return $res->fetchAll(PDO::FETCH_ASSOC);
}

function d($table, $params)
{
    $bdd = BDD::get_instance();
    if (!$bdd->isLoaded()) return false;

    if (gettype($params) == 'array')
    {
        $i = 0;
        $req = "DELETE FROM " . $table;
        if (count($params) > 0) $req .= ' WHERE ';
        foreach ($params as $champ => $valeur) {
            if ($i > 0) $req .= " AND ";
            if (gettype($valeur) == "NULL") $req .= $champ . " IS NULL";
            else $req .= $champ . " = '" . $valeur . "'";
            $i = 1;
        }
    }
    else $req = "DELETE FROM {$table} WHERE id = {$params}";
    $res = $bdd->query($req);
    if (!$res) {
        $bdd->set_erreur($bdd->errorInfo());
        return false;
    }
    return true;
}

function l($option = false)
{
    if (gettype($option) == 'boolean'){
        switch ($option){
            case true:
                die(BDD::get_instance()->get_erreur());
                break;
            case false:
                echo BDD::get_instance()->get_erreur();
                break;
        }
    }else if (gettype($option) == 'string'){
        switch ($option){
            case 'return':
                return BDD::get_instance()->get_erreur();
                break;
            default:
                $_SESSION[$option] = BDD::get_instance()->get_erreur();
                break;
        }
    }
    return false;
}