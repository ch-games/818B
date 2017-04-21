<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Pure-PHP implementation of MD5.
 *
 * Uses mcrypt, if available, and an internal implementation, otherwise.
 *
 * PHP versions 4 and 5
 *
 * If {@link Crypt_MD5::setKeyLength() setKeyLength()} isn't called, it'll be calculated from
 * {@link Crypt_MD5::setKey() setKey()}.  ie. if the key is 128-bits, the key length will be 128-bits.  If it's 136-bits
 * it'll be null-padded to 160-bits and 160 bits will be the key length until {@link Crypt_Rijndael::setKey() setKey()}
 * is called, again, at which point, it'll be recalculated.
 *
 * Since Crypt_MD5 extends Crypt_Rijndael, some functions are available to be called that, in the context of MD5, don't
 * make a whole lot of sense.  {@link Crypt_MD5::setBlockLength() setBlockLength()}, for instance.  Calling that function,
 * however possible, won't do anything (MD5 has a fixed block length whereas Rijndael has a variable one).
 *
 * Here's a short example of how to use this library:
 * <code>
 * <?php
 *    include('Crypt/MD5.php');
 *
 *    $md5 = new Crypt_MD5();
 *
 *    $md5->setKey('abcdefghijklmnop');
 *
 *    $size = 10 * 1024;
 *    $plaintext = '';
 *    for ($i = 0; $i < $size; $i++) {
 *        $plaintext.= 'a';
 *    }
 *
 *    echo $md5->decrypt($md5->encrypt($plaintext));
 * ?>
 * </code>
 *
 * LICENSE: Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @category   Crypt
 * @package    Crypt_MD5
 * @author     Jim Wigginton <terrafrost@php.net>
 * @copyright  MMVIII Jim Wigginton
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link       http://phpseclib.sourceforge.net
 */

/**#@+
 * @access public
 * @see Crypt_MD5::encrypt()
 * @see Crypt_MD5::decrypt()
 */
/**
 * Encrypt / decrypt using the Counter mode.
 *
 * Set to -1 since that's what Crypt/Random.php uses to index the CTR mode.
 *
 * @link http://en.wikipedia.org/wiki/Block_cipher_modes_of_operation#Counter_.28CTR.29
 */
define('CRYPT_MD5_MODE_CTR', -1);
/**
 * Encrypt / decrypt using the Electronic Code Book mode.
 *
 * @link http://en.wikipedia.org/wiki/Block_cipher_modes_of_operation#Electronic_codebook_.28ECB.29
 */
define('CRYPT_MD5_MODE_ECB', 1);
/**
 * Encrypt / decrypt using the Code Book Chaining mode.
 *
 * @link http://en.wikipedia.org/wiki/Block_cipher_modes_of_operation#Cipher-block_chaining_.28CBC.29
 */
define('CRYPT_MD5_MODE_CBC', 2);
/**
 * Encrypt / decrypt using the Cipher Feedback mode.
 *
 * @link http://en.wikipedia.org/wiki/Block_cipher_modes_of_operation#Cipher_feedback_.28CFB.29
 */
define('CRYPT_MD5_MODE_CFB', 3);
/**
 * Encrypt / decrypt using the Cipher Feedback mode.
 *
 * @link http://en.wikipedia.org/wiki/Block_cipher_modes_of_operation#Output_feedback_.28OFB.29
 */
define('CRYPT_MD5_MODE_OFB', 4);
/**#@-*/

/**#@+
 * @access private
 * @see Crypt_MD5::Crypt_MD5()
 */
/**
 * Toggles the internal implementation
 */
define('CRYPT_MD5_MODE_INTERNAL', 1);
/**
 * Toggles the mcrypt implementation
 */
define('CRYPT_MD5_MODE_MCRYPT', 2);
/**#@-*/

/**
 * Pure-PHP implementation of MD5.
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 * @version 0.1.0
 * @access  public
 * @package Crypt_MD5
 */
class CRYPT_MD5 {
    /**
     * mcrypt resource for encryption
     *
     * The mcrypt resource can be recreated every time something needs to be created or it can be created just once.
     * Since mcrypt operates in continuous mode, by default, it'll need to be recreated when in non-continuous mode.
     *
     * @see Crypt_MD5::encrypt()
     * @var String
     * @access private
     */
    var $enmcrypt;

    /**
     * mcrypt resource for decryption
     *
     * The mcrypt resource can be recreated every time something needs to be created or it can be created just once.
     * Since mcrypt operates in continuous mode, by default, it'll need to be recreated when in non-continuous mode.
     *
     * @see Crypt_MD5::decrypt()
     * @var String
     * @access private
     */
    var $demcrypt;

    /**
     * mcrypt resource for CFB mode
     *
     * @see Crypt_MD5::encrypt()
     * @see Crypt_MD5::decrypt()
     * @var String
     * @access private
     */
    var $ecb;

    /**
     * Default Constructor.
     *
     * Determines whether or not the mcrypt extension should be used.  $mode should only, at present, be
     * CRYPT_MD5_MODE_ECB or CRYPT_MD5_MODE_CBC.  If not explictly set, CRYPT_MD5_MODE_CBC will be used.
     *
     * @param optional Integer $mode
     * @return Crypt_MD5
     * @access public
     */
    function Crypt_MD5($mode = CRYPT_MD5_MODE_CBC)
    {
    }

     function sign($query,$md5key){
        if(empty($query)||empty($md5key)){
             return false;
         }ksort($query);
        $tempValstr="";
        foreach($query as $k=>$v){
            $tempValstr.=$v;
        }
        $finallyStr=$tempValstr.$md5key;
        $sign= md5($finallyStr);
        return $sign;
    }


}
?>