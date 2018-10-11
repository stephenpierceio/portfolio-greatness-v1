<?php
/**
 * Post It Module
 *
 * A module to display notes in a post-it-note style.
 * 
 * @author Polished Geek
 * @package mod_postit
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
 * PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS BE LIABLE FOR ANY CLAIM,
 * DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class modPostitHelper

{

    /**

     * Retrieves the hello message

     *

     * @param array $params An object containing the module parameters

     * @access public

     */    

    public static function getPostit( $params )

    {

        return 'Hello, World!';

    }

}

?>