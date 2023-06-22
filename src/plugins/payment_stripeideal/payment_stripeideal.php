<?php
/**
 * @version		$Id: payment_stripe.php 1930 2017-11-24 14:04:05Z gfisch $
 * @package		mymuse
 * @copyright	Copyright © 2010 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@joomlamymuse.com
 * @website		http://www.joomlamymuse.com
 */
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\Language\Language;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\ParameterType;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;
use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Categories\CategoryNode;
use Joomla\Component\Mymuse\Administrator\Table\OrderTable;

/**
* MyMuse PaymentStripeIdeal plugin
*
* @package 		MyMuse
* @subpackage	mymuse
*/

require_once(__DIR__.'/vendor/autoload.php');

class plgMymusePayment_Stripeideal extends CMSPlugin 
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;
	
	var $stripe = array();
	
	/**
	 * Constructor
	 *
	 * @param   object  $subject  The object to observe
	 * @param   array   $config   An array that holds the plugin configuration
	 */
	public function __construct(&$subject, $config)
	{
		$this->plgMymusePayment_Stripeideal($subject, $config);
	}
	
	function plgMymusePayment_Stripeideal(&$subject, $config)  {
		parent::__construct($subject, $config);

		$document       = Factory::getDocument();
		$document->addScript( 'https://js.stripe.com/v3/' );

		$document->addScript( 'https://polyfill.io/v3/polyfill.min.js?version=3.52.1&features=fetch' );
		$this->stripe = array(
				"secret_key"      => $this->params->get('my_stripe_private_key'),
				"public_key" => $this->params->get('my_stripe_public_key')
		);
		
		\Stripe\Stripe::setApiKey($this->stripe['secret_key']);
		\Stripe\Stripe::setAppInfo(
  "Joomla MyMuse Payments - Stripe",
  "5.0.0",
  "https://www.joomlamymuse.com"
);
	}
	
	
	
	/**
	 * Stripe Payment form
	 * onBeforeMyMusePayment
	 */
	function onBeforeMyMusePayment($shopper, $store, $order, $params, $Itemid=1 )
	{
		
		$document = JFactory::getDocument();
		$mainframe 	= JFactory::getApplication();
		$currency 		= $store->currency;
		$amount 		= preg_replace("/\./","",sprintf("%.2f", $order->order_total));
		$shopper->first_name 	= isset($shopper->profile['first_name'])? $shopper->profile['first_name'] : '';
		$shopper->last_name 	= isset($shopper->profile['last_name'])? $shopper->profile['last_name'] : '';
	
		$intent = \Stripe\PaymentIntent::create([
		    'amount' => $amount,
		    'currency' => $currency,
		    'payment_method_types' => ['card','ideal'],
		]);

		$PAYMENT_INTENT_CLIENT_SECRET = $intent->client_secret;

		/**
		* REQUEST Array to send
		Array
		(
		    [payment_intent] => 
		    [order_number] => d805e908185501f317a805ab9e89306e
		    [currency] => EUR
		    [total] => 20.00
		    [redirect_status] => succeeded
		)
		*/
		$return_url = JURI::root().'index.php?option=com_mymuse&task=notify&order_number='.$order->order_number.'&total='.$order->order_total.'&currency='.$store->currency.'&redirect_status=succeeded&payment_intent=1';
		

$string = '<script>
jQuery(document).ready(function($){

	var stripe = Stripe("'.$this->stripe['public_key'].'");
	var elements = stripe.elements();

	// Custom styling can be passed to options when creating an Element.
	var style = {
	  base: {
	    // Add your base input styles here. For example:
	    fontSize: "16px",
	    color: "#32325d",
	    border: "1px solid #666",
	  },
	};
	';

if($this->params->get('my_stripe_payment') == "ideal" || $this->params->get('my_stripe_payment') == "both"){

	$string .= '

	// Create an instance of the idealBank Element
	var idealBank = elements.create(\'idealBank\', {});

	// Add an instance of the idealBank Element into
	// the `ideal-bank-element` <div>
	idealBank.mount(\'#ideal-bank-element\');

	


	var form_ideal = document.getElementById(\'payment-form\');
	var accountholderName = document.getElementById(\'accountholder-name\');
	form_ideal.addEventListener(\'submit\', function(event) {
	  event.preventDefault();
	  // Redirects away from the client
	  stripe.confirmIdealPayment(
	    \''.$PAYMENT_INTENT_CLIENT_SECRET.'\',
	    {
	      payment_method: {
	        ideal: idealBank,
	        billing_details: {
	          name: accountholderName.value,
	        },
	      },
	      return_url: \''.$return_url.'\',
	    }
	  );
	});
';
}

if($this->params->get('my_stripe_payment') == "card" || $this->params->get('my_stripe_payment') == "both"){

	$string .= '
	// Create an instance of the card Element
	var cardElement = elements.create(\'card\', {});

	// Add an instance of the card Element into
	// the `card-bank-element` <div>
	cardElement.mount(\'#card-bank-element\');



	var form_card = document.getElementById(\'payment-form-card\');
	form_card.addEventListener(\'submit\', function(event) {
	  event.preventDefault();
	  payWithCard(stripe, cardElement, \''.$PAYMENT_INTENT_CLIENT_SECRET.'\');
	});


	// Calls stripe.confirmCardPayment
	// If the card requires authentication Stripe shows a pop-up modal to
	// prompt the user to enter authentication details without leaving your page.
	var payWithCard = function(stripe, card, clientSecret) {

	  loading(true);
	  stripe
	    .confirmCardPayment(clientSecret, {
	      payment_method: {
	        card: card
	      }
	    })
	    .then(function(result) {
	      if (result.error) {
	        // Show error to your customer
	        showError(result.error.message);
	      } else {
	        // The payment succeeded!
	        orderComplete(result.paymentIntent.id);
	      }
	    });
	};

	/* ------- UI helpers ------- */
	// Shows a success message when the payment is complete
	var orderComplete = function(paymentIntentId) {
	  loading(false);

	  window.location.replace("'.$return_url.'");

	  /**document
	    .querySelector(".result-message a")
	    .setAttribute(
	      "href",
	      "https://dashboard.stripe.com/test/payments/" + paymentIntentId
	    );
	  document.querySelector(".result-message").classList.remove("hidden");
	  document.querySelector("button").disabled = true;
	  */
	};

	// Show the customer the error from Stripe if their card fails to charge
	var showError = function(errorMsgText) {
	  loading(false);
	  var errorMsg = document.querySelector("#card-error");
	  errorMsg.textContent = errorMsgText;
	  setTimeout(function() {
	    errorMsg.textContent = "";
	  }, 4000);
	};
	// Show a spinner on payment submission
	var loading = function(isLoading) {
	  if (isLoading) {
	    // Disable the button and show a spinner
	    document.querySelector("button").disabled = true;
	    document.querySelector("#spinner").classList.remove("hidden");
	    document.querySelector("#button-text").classList.add("hidden");
	  } else {
	    document.querySelector("button").disabled = false;
	    document.querySelector("#spinner").classList.add("hidden");
	    document.querySelector("#button-text").classList.remove("hidden");
	  }
	};
	';

}

	$string .= '	
});
</script>
 <div class="form-row">
';



if($this->params->get('my_stripe_payment') == "card" || $this->params->get('my_stripe_payment') == "both"){

	$string .= '
	<div class="mymuse-payment-form">
		<form action="'. $return_url.'" method="post" id="payment-form-card">
			<label for="ideal-bank-element">
			  '.Text::_('COM_MYMUSE_STRIPE_CARD_LABEL_TEXT').'
			</label>
		    <div id="card-bank-element">
		      <!-- A Stripe Element will be inserted here. -->
		    </div>

		    <!-- Used to display form errors. -->
		  	
		  <button id="submit" class="button uk-button ">
        	<div class="spinner hidden" id="spinner"></div>
		  	<span id="button-text">'.Text::_('COM_MYMUSE_STRIPE_IDEAL_SUBMIT_TEXT').'</span>
		  </button>
		  <div id="card-error" role="alert"></div>
		  <div class="result-message hidden">
        Payment succeeded, see the result in your
        <a href="" target="_blank">Stripe dashboard.</a> Refresh the page to pay again.

      	</div>
		</form>
	</div>
	';
}
if($this->params->get('my_stripe_payment') == "ideal" || $this->params->get('my_stripe_payment') == "both"){

	$string .= '
	 	<div class="mymuse-payment-form">
			<form action="'. $return_url.'" method="post" id="payment-form">
				<label for="ideal-bank-element">
			      '.Text::_('COM_MYMUSE_STRIPE_IDEAL_LABEL_TEXT').'
			    </label>
			  <div>
			    <label for="accountholder-name">
			      '.Text::_('COM_MYMUSE_STRIPE_NAME_TEXT').'
			    </label>
			    <input id="accountholder-name"  name="accountholder-name" value="'.$shopper->name.'">
			  </div>
			  <div>
			    
			    <div id="ideal-bank-element">
			      <!-- A Stripe Element will be inserted here. -->
			    </div>
			    <!-- Used to display form errors. -->
			  	<div id="error-message" role="alert"></div>
			  	<button class="button uk-button ">'.Text::_('COM_MYMUSE_STRIPE_IDEAL_SUBMIT_TEXT').'</button>
			  </div>
			</form>
		</div>
	</div>
	';
}
  		//'. JURI::root().'index.php?option=com_mymuse&task=notify
		return $string;
	}
	/**
	 * notify
	 * catch the IPN post from Stripe, return required responses, update orders and do mailouts
	 * 
	 */
	function onMyMuseNotify($params)
	{
		$mainframe 	= JFactory::getApplication();
		
		/**
		 * REQUEST Array
Array
(
    [payment_intent] => 
    [order_number] => d805e908185501f317a805ab9e89306e
    [currency] => EUR
    [total] => 20.00
    [redirect_status] => succeeded
)
*/
		$db	= JFactory::getDBO();
		$date = date('Y-m-d h:i:s');
		$debug = "#####################\nStripe notify PLUGIN\n";
		$result = array();
		$result['plugin'] 				= "payment_stripeideal";
		$result['myorder'] 				= 0; //must be >0 to trigger that it was this plugin
		$result['message_sent'] 		= 0; //must be >0 or tiggers error
		$result['message_received'] 	= 0; //must be >0 or tiggers error
		$result['order_found']			= 0; //must be >0 or tiggers error
		$result['order_verified'] 		= 0; //must be >0 or tiggers error
		$result['order_completed'] 		= 0; //must be >0 or tiggers error
		$result['order_number']			= 0; //must be >0 or tiggers error
		$result['order_id']				= 0; //must be >0 or tiggers error
		$result['payer_email']			= 0; 
		$result['payment_status']		= 0;
		$result['txn_id']				= 0;
		$result['error']				= '';
		if(!isset($_REQUEST['payment_intent'])){
			//wasn't stripe
			$debug .= "Was not Stripeideal. \n";
			$debug .= "-------END-------";
			if($params->get('my_debug')){
				
        		MymuseHelper::logMessage( $debug  );
  			}
  			return $result;
		}else{
			$debug .= print_r($_REQUEST, true);
			if($params->get('my_debug')){
        		MymuseHelper::logMessage( $debug  );
  			}
		}

		//we are in stipeideal
		$result['myorder'] = 1;

		
		//get order
		if(!isset($_REQUEST['order_number'])){
			//missing invoice
			$debug .= "Missing Invoice! \n";
			$debug .= "-------END-------";
			if($params->get('my_debug')){
				MymuseHelper::logMessage( $debug  );
			}
			$result['error'] = "Missing Invoice!";
			$result['redirect'] = "index.php?option=com_mymuse&view=cart";
			return $result;
		}

		$result['order_number'] = $_REQUEST['order_number'];
		$query = "SELECT * FROM `#__mymuse_order`
                    WHERE `order_number`='".$result['order_number']."'";
		$date = date('Y-m-d h:i:s');
		$debug = "$date  1.1 $query \n\n";
		
		$db->setQuery($query);
		if(!$this_order = $db->loadObject()){
			$debug .= "1.2 !!!!Error no order object: ".$db->_errorMsg."\n\n";
			$debug .= "-------END-------";
			if($params->get('my_debug')){
				MymuseHelper::logMessage( $debug  );
			}
			$result['error'] = "Could not find order!";
			$result['redirect'] = "index.php?option=com_mymuse&view=cart";
			return $result;
		}
		$result['order_id'] 	= $this_order->id;

	
		$result['message_sent'] 		= 1;
		$result['message_received'] 	= 1; 
		if($_REQUEST['redirect_status'] == "succeeded"){
			$result ['order_verified'] = 1;
			$result['payment_status'] = "Completed";
			$result ['txn_id'] = $REQUEST ['payment_intent'];
			$result ['transaction_id'] = $REQUEST ['payment_intent'];
			$result ['amountin'] = $_REQUEST ['total'];
			$result ['currency'] = $_REQUEST ['currency'];
			$result ['transaction_status'] = 1;
			
        	// update the payment status
        	$result ['order_found']  = 1;
        	$result ['order_id'] 	= $this_order->id;
        	$result ['order_number'] = $this_order->order_number;
        	
        	$MymuseHelper = new MymuseHelper();
            $MymuseHelper->orderStatusUpdate($result['order_id'] , "C");
            $date = date('Y-m-d h:i:s');
            $debug .= "$date 5. order COMPLETED at Stripe, update in DB\n\n";
            $result['order_completed'] = 1;
            $result['redirect'] = JURI::root().'index.php?option=com_mymuse&task=thankyou&view=cart&pp=stripeideal&st=Completed';
        	
		}else{
			$date = date('Y-m-d h:i:s');
			$debug = "$date 5. Stripe iDeal, returned 'failed'\n\n";
			$result['order_completed'] = 0;
			if($params->get('my_debug')){
				MymuseHelper::logMessage( $debug  );
			}
			$result['error'] = $debug;
			$result['redirect'] = "index.php?option=com_mymuse&view=cart";
		}
		
        $date = date('Y-m-d h:i:s');
        $debug .= "$date Finished talking to Stripe \n\n";
		$debug .= "-------END PLUGIN-------";
  		if($params->get('my_debug')){
        	MymuseHelper::logMessage( $debug  );
  		}
  		

        return $result;
	}
	
	/**
	function generatePaymentResponse($intent) {
	    if ($intent->status == 'requires_action' &&
	        $intent->next_action->type == 'use_stripe_sdk') {
	        # Tell the client to handle the action
	        echo json_encode([
	            'requires_action' => true,
	            'payment_intent_client_secret' => $intent->client_secret
	        ]);
	    } else if ($intent->status == 'succeeded') {
	        # The payment didn’t need any additional actions and completed!
	        # Handle post-payment fulfillment
	        echo json_encode([
	            'success' => true
	        ]);
	    } else {
	        # Invalid status
	        http_response_code(500);
	        echo json_encode(['error' => 'Invalid PaymentIntent status']);
	    }
	}
	*/
	function onAfterMyMusePayment()
	{
		$email_msg = '';
		if($this->params->get('email_msg')){
			$email_msg = "payment_stripe:".preg_replace("/\\n/","<br />",$this->params->get('email_msg'));
		}
		return $email_msg;
	
	}
}
?>