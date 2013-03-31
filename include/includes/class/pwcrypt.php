<?php
/**
 * @license http://opensource.org/licenses/gpl-2.0.php The GNU General Public License (GPL)
 * @copyright (C) 2000-2012 ilch.de
 */
defined('main') or die('no direct access');

/**
 * PwCrypt
 * 
 * Achtung: beim �bertragen von mit 2a erzeugten Passw�rtern auf einen anderen PC/Server,
 * dort kann es u.U. Passieren, dass eine Authentifikation nicht mehr m�glich ist,
 * da 2a auf einigen System fehlerhafte Ergebnisse liefert.
 * Versuche dann bitte 2x bzw. 2y.
 *
 * @author finke <Surf-finke@gmx.de>
 * @copyright Copyright (c) 2012
 */
class PwCrypt
{
    const LETTERS = 1;    //0001
    const NUMBERS = 2;    //0010
    const ALPHA_NUM = 3;    //0011
    const URL_CHARACTERS = 4;   //0100
    const FOR_URL = 7;    //0111
    const SPECIAL_CHARACTERS = 8; //1000
    //Konstanten f�r die Verschl�sselung
    const MD5 = '1';
    const BLOWFISH_OLD = '2a';
    const BLOWFISH = '2y';
    const BLOWFISH_FALSE = '2x';
    const SHA256 = '5';
    const SHA512 = '6';

    private $hashAlgorithm = self::SHA256;

    /**
     * @param string $lvl Gibt den zu verwendenden Hashalgorithmus an (Klassenkonstante)
     */
    public function __construct($lvl = '')
    {
        if (!empty($lvl)) {
            $this->hashAlgorithm = $lvl;
        }

        /* Wenn 2a gew�hlt aber 2y verf�gbar: nutze trotzdem 2y, da dies sicherer ist; wenn 2x oder 2y gew�hlt
         * aber nicht verf�gbar, nutze 2a */
        if (version_compare(PHP_VERSION, '5.3.5', '<')
            && ($this->hashAlgorithm === self::BLOWFISH || $this->hashAlgorithm === self::BLOWFISH_FALSE)
        ) {
            $this->hashAlgorithm = self::BLOWFISH_OLD;
        } elseif (version_compare(PHP_VERSION, '5.3.5', '>=') && $this->hashAlgorithm == self::BLOWFISH_OLD) {
            $this->hashAlgorithm = self::BLOWFISH;
        }

        // Pr�fen welche Hash Funktionen Verf�gbar sind. Ab 5.3 werden alle Mitgeliefert
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            if ($this->hashAlgorithm === self::SHA512 && (!defined('CRYPT_SHA512') || CRYPT_SHA512 !== 1)) {
                $this->hashAlgoriathm = self::SHA256; // Wenn SHA512 nicht verf�gbar, versuche SHA256
            }
            if ($this->hashAlgorithm === self::SHA256 && (!defined('CRYPT_SHA256') || CRYPT_SHA256 !== 1)) {
                $this->hashAlgorithm = self::BLOWFISH_OLD; // Wenn SHA256 nicht verf�gbar, versuche BLOWFISH
            }
            if ($this->hashAlgorithm === self::BLOWFISH_OLD && (!defined('CRYPT_BLOWFISH') || CRYPT_BLOWFISH !== 1)) {
                $this->hashAlgorithm = self::MD5; // Wenn BLOWFISH nicht verf�gbar, nutze MD5
            }
        }
    }

    /**
     * Erstellt eine zuf�llige Zeichenkette
     *
     * @param integer $size L�nge der Zeichenkette
     * @param integer $chars Angabe welche Zeichen f�r die Zeichenkette verwendet werden
     * @return string
     */
    public static function getRndString($size = 20, $chars = self::LETTERS)
    {
        if ($chars & self::LETTERS) {
            $pool = 'abcdefghijklmnopqrstuvwxyz';
            $pool .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }

        if ($chars & self::NUMBERS) {
            $pool .='0123456789';
        }

        //in einer URL nicht reservierte Zeichen
        if ($chars & (self::URL_CHARACTERS | self::SPECIAL_CHARACTERS)) {
            $pool .= '-_.~';
        }

        //restiliche Sonderzeichen
        if ($chars & self::SPECIAL_CHARACTERS) {
            $pool .= '!#$%&()*+,/:;=?@[]';
        }

        $pool = str_shuffle($pool);
        $pool_size = strlen($pool);
        $string = '';
        for ($i = 0; $i < $size; $i++) {
            //TODO: Zufallszahlen aus /dev/random bzw /dev/urandom wenn verf�gbar
            $string .= $pool[mt_rand(0, $pool_size - 1)];
        }
        return $string;
    }

    /**
     * Pr�ft, ob der �bergebene Hash, im crpyt Format ist
     *
     * @param mixed $hash
     * @return boolean
     */
    public static function isCryptHash($hash)
    {
        return (preg_match('/^\$([156]|2[axy])\$/', $hash) === 1);
    }

    /**
     * Gibt den Code der gew�hlten/genutzen Hashmethode zur�ck (Crpyt Konstante)
     *
     * @return string
     */
    public function getHashAlgorithm()
    {
        return $this->hashAlgorithm;
    }

    /**
     * Erstellt ein Hash f�r das �bergebene Passwort
     *
     * @param string $passwd Klartextpasswort
     * @param string $salt Salt f�r den Hashalgorithus
     * @param integer $rounds Anzahl der Runden f�r den verwendeten Hashalgorithmus
     * @return string Hash des Passwortes (Ausgabe von crypt())
     */
    public function cryptPasswd($passwd, $salt = '', $rounds = 0)
    {
        $salt_string = '';
        switch ($this->hashAlgorithm) {
            case self::SHA512:
            case self::SHA256:
                $salt = (empty($salt) ? self::getRndString(16, self::LETTERS | self::NUMBERS) : $salt);
                if ($rounds < 1000 || $rounds > 999999999) {
                    $rounds = mt_rand(2000, 10000);
                }
                $salt_string = '$' . $this->hashAlgorithm . '$rounds=' . $rounds . '$' . $salt . '$';
                break;
            case self::BLOWFISH:
            case self::BLOWFISH_OLD:
            case self::BLOWFISH_FALSE:
                $salt = (empty($salt) ? self::getRndString(22, self::LETTERS | self::NUMBERS) : $salt);
                if ($rounds < 4 || $rounds > 31) {
                    $rounds = mt_rand(6, 10);
                }
                //Verwendet 2x, wenn verf�gbar, auch wenn 2a angegeben wurde
                $salt_string = '$' . $this->hashAlgorithm . '$' . $rounds . '$' . $salt . '$';
                break;
            case self::MD5:
                $salt = (empty($salt) ? self::getRndString(12, self::LETTERS | self::NUMBERS) : $salt);
                $salt_string = '$' . $this->hashAlgorithm . '$' . $salt . '$';
                break;
            default:
                return false;
        }
        $crypted_pw = crypt($passwd, $salt_string);
        if (strlen($crypted_pw) < 13) {
            return false;
        }
        return $crypted_pw;
    }

    /**
     * Pr�ft, ob das Klartextpasswort dem Hash "entspricht"
     *
     * @param mixed $passwd Klartextpasswort
     * @param mixed $crypted_passwd Hash des Passwortes (aus der Datenbank)
     * @param boolean $backup wenn Check fehlschl�gt und das alte passwort mit BLOWFISH_OLD verschl�sselt wurde,
     *      werden beide Varianten noch einmal explizit gepr�ft, wenn verf�gbar. Nur nach Transfer der Datenbank verwenden,
     *      da dies ein Sicherheitsrisiko darstellen kann
     * @return boolean
     */
    public function checkPasswd($passwd, $crypted_passwd, $backup = false)
    {
        if (empty($crypted_passwd)) {
            return false;
        }
        if (self::isCryptHash($crypted_passwd)) {
            $new_chrypt_pw = crypt($passwd, $crypted_passwd);
            if (strlen($new_chrypt_pw) < 13) {
                return false;
            }
        } else {
            $new_chrypt_pw = md5($passwd);
        }
        if ($new_chrypt_pw == $crypted_passwd) {
            return true;
        } else {
            if ($backup == true
                && version_compare(PHP_VERSION, '5.3.5', '>=')
                && substr($crypted_passwd, 0, 4) == '$2a$'
            ) {
                $password_x = '$2x$' . substr($crypted_passwd, 4);
                $password_y = '$2y$' . substr($crypted_passwd, 4);
                $password_neu_x = crypt($passwd, $password_x);
                $password_neu_y = crypt($passwd, $password_y);
                if ($password_neu_x === $password_x || $password_neu_y === $password_y) {
                    return true;
                }
            }
        }
        return false;
    }
}
