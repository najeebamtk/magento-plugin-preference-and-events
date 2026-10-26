<?php
namespace Codilar\HelloWorld\Model;

use Magento\Framework\Exception\InputException;

class AccountManagement extends \Magento\Customer\Model\AccountManagement
{
    /**
     * Words that make a weak password base (checked after removing endings and letter swaps)
     */
    private const WEAK_WORDS = [
        'password', 'passwd', 'welcome', 'qwerty', 'admin', 'administrator', 'login',
        'letmein', 'iloveyou', 'india', 'test', 'abcd', 'user', 'customer',
        'magento', 'codilar', 'shirt', 'shopping',
    ];

    /**
     * Common letter swaps people use to "hide" a word, e.g. P@ssw0rd
     */
    private const LETTER_SWAPS = ['@' => 'a', '4' => 'a', '0' => 'o', '1' => 'i', '!' => 'i', '3' => 'e', '$' => 's', '5' => 's', '7' => 't'];

    /**
     * Keyboard and alphabet orders used to detect sequences like 12345 or qwert
     */
    private const SEQUENCES = ['0123456789', 'abcdefghijklmnopqrstuvwxyz', 'qwertyuiop', 'asdfghjkl', 'zxcvbnm'];

    /**
     * Minimum length of an in-order sequence that is rejected
     */
    private const SEQUENCE_LENGTH = 5;

    /**
     * Overrides Magento's PROTECTED checkPasswordStrength(): Magento's checks first, then our rules
     *
     * @param string $password
     * @return void
     * @throws InputException
     */
    protected function checkPasswordStrength($password)
    {
        parent::checkPasswordStrength($password);

        $lower = strtolower((string) $password);

        if ($this->hasWeakBaseWord($lower)) {
            throw new InputException(
                __('This password is based on a common word and is easy to guess. Please choose a different one.')
            );
        }

        if (preg_match('/(.)\1{3,}/', $lower)) {
            throw new InputException(
                __('The password must not repeat the same character 4 or more times in a row.')
            );
        }

        if ($this->hasSequence($lower)) {
            throw new InputException(
                __('The password must not contain easy sequences like "12345", "abcde" or "qwert".')
            );
        }
    }

    /**
     * Password@2026 -> "password", P@ssw0rd! -> "password"
     *
     * @param string $password
     * @return bool
     */
    private function hasWeakBaseWord($password)
    {
        $base = preg_replace('/[^a-z]+$/', '', $password);
        $base = strtr($base, self::LETTER_SWAPS);
        $base = preg_replace('/[^a-z]/', '', $base);

        return in_array($base, self::WEAK_WORDS, true);
    }

    /**
     * True if the password contains SEQUENCE_LENGTH characters in a row from any sequence (either direction)
     *
     * @param string $password
     * @return bool
     */
    private function hasSequence($password)
    {
        foreach (self::SEQUENCES as $sequence) {
            foreach ([$sequence, strrev($sequence)] as $order) {
                for ($i = 0; $i <= strlen($order) - self::SEQUENCE_LENGTH; $i++) {
                    if (strpos($password, substr($order, $i, self::SEQUENCE_LENGTH)) !== false) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
