/**
 * This file holds javscript functions that are used by the templates in the Theme
 * 
 */
 
 // AJAX FUNCTIONS 
function loadNewPage( el, url ) {
	
	var theEl = $(el);
	var callback = {
		success : function(responseText) {
			theEl.innerHTML = responseText;
			if( Slimbox ) Slimbox.scanPage();
		}
	}
	var opt = {
	    // Use POST
	    method: 'get',
	    // Handle successful response
	    onComplete: callback.success
    }
	new Ajax( url + '&only_page=1', opt ).request();
}

function handleGoToCart() { document.location = live_site + '/index.php?option=com_virtuemart&page=shop.cart&product_id=' + formCartAdd.product_id.value + '&Itemid=' +formCartAdd.Itemid.value; }

var timeoutID = 0;

function handleAddToCart( formId, parameters ) {
	formCartAdd = document.getElementById( formId );
	
	var callback = function(responseText) {
		updateMiniCarts();
		// close an existing mooPrompt box first, before attempting to create a new one (thanks wellsie!)
		if (document.boxB) {
			document.boxB.close();
			clearTimeout(timeoutID);
		}

		document.boxB = new MooPrompt(notice_lbl, responseText, {
				buttons: 2,
				width:400,
				height:150,
				overlay: false,
				button1: ok_lbl,
				button2: cart_title,
				onButton2: 	handleGoToCart
			});
			
		//setTimeout( 'document.boxB.close()', 3000 );
	}
	
	var opt = {
	    // Use POST
	    method: 'post',
	    // Send this lovely data
	    data: $(formId),
	    // Handle successful response
	    onComplete: callback,
	    
	    evalScripts: true
	}

	new Ajax(formCartAdd.action, opt).request();
}
/**
* This function searches for all elements with the class name "vmCartModule" and
* updates them with the contents of the page "shop.basket_short" after a cart modification event
*/
function updateMiniCarts() {
	var callbackCart = function(responseText) {
		carts = $$( '.vmCartModule' );
		if( carts ) {
			try {
				for (var i=0; i<carts.length; i++){
					carts[i].innerHTML = responseText;
		
					try {
						color = carts[i].getStyle( 'color' );
						bgcolor = carts[i].getStyle( 'background-color' );
						if( bgcolor == 'transparent' ) {
							// If the current element has no background color, it is transparent.
							// We can't make a highlight without knowing about the real background color,
							// so let's loop up to the next parent that has a BG Color
							parent = carts[i].getParent();
							while( parent && bgcolor == 'transparent' ) {
								bgcolor = parent.getStyle( 'background-color' );
								parent = parent.getParent();
							}
						}
						var fxc = new Fx.Style(carts[i], 'color', {duration: 1000});
						var fxbgc = new Fx.Style(carts[i], 'background-color', {duration: 1000});

						fxc.start( '#222', color );				
						fxbgc.start( '#fff68f', bgcolor );
						if( parent ) {
							setTimeout( "carts[" + i + "].setStyle( 'background-color', 'transparent' )", 1000 );
						}
					} catch(e) {}
				}
			} catch(e) {}
		}
	}
	var option = { method: 'post', onComplete: callbackCart, data: { only_page:1,page: "shop.basket_short", option: "com_virtuemart" } }
	new Ajax( live_site + '/index2.php', option).request();
	

} 
/**
* This function allows you to present contents of a URL in a really nice stylish dhtml Window
* It uses the WindowJS, so make sure you have called
* vmCommonHTML::loadWindowsJS();
* before
*/
function fancyPop( url, parameters ) {
	
	parameters = parameters || {};
	popTitle = parameters.title || '';
	popWidth = parameters.width || 700;
	popHeight = parameters.height || 600;
	popModal = parameters.modal || false;
	
	window_id = new Window('window_id', {className: "mac_os_x", 
										title: popTitle,
										showEffect: Element.show,
										hideEffect: Element.hide,
										width: popWidth, height: popHeight}); 
	window_id.setAjaxContent( url, {evalScripts:true}, true, popModal );
	window_id.setCookie('window_size');
	window_id.setDestroyOnClose();
}




// ------------------

var yes_lbl = 'Подписаться на уведомление';
var email_lbl = 'Укажите email';
var cfrm_lbl = 'Мы обязательно вам сообщим';

message = function () {
    document.boxB = new MooPrompt(email_lbl, responseText, {
        buttons: 1,
        width: 300,
        height: 125,
        overlay: false,
        button1: yes_lbl,
        onButton1: addEmail
    });
}
addEmail = function () {
    var product_sku = document.getElementById('product_sku');
    var user_email = document.getElementById('user_email');
    if (isValidEmailAddress(user_email.value) !== true) {
        buffer_lbl = user_email.value;
        message();
        setTimeout(function () {
            var user_email = document.getElementById('user_email');
            user_email.style.borderColor = 'red';
            user_email.value = buffer_lbl;
        }, 300);
    }
    else {
        var data = {product_sku: product_sku.value, user_email: user_email.value};
        var opt = {
            // Use POST
            method: 'post',
            // Send this lovely data
            data: data,
            // Handle successful response
            onComplete: function (response) {
                if (document.boxB) {
                    clearTimeout(timeoutID);
                }
                document.boxB = new MooPrompt("Подписка на уведомление", response, {
                    buttons: 0,
                    width: 300,
                    height: 125,
                    delay: 5000,
                    overlay: false
                });
            },
            evalScripts: true
        }
        new Ajax("/index.php?option=com_virtuemart&page=shop.ajaxmaillist.php", opt).request();
    }
};

function isValidEmailAddress(emailAddress) {
    var pattern = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
    return pattern.test(emailAddress);
}
addOrderUnion = function () {
    var order_id = jQuery('#label_add_child').val();
    var cur_order_id = jQuery('#order_id').val();
    var radio_choise = jQuery('input[name=action_child_order]:checked').attr('id');

    var opt = {
        method: 'post',
        data: {order_id: order_id, cur_order_id: cur_order_id, radio_choise: radio_choise},
        onComplete: function (response) {
            location.reload();
        },
        evalScripts: true
    }
    new Ajax("/index.php?option=com_virtuemart&page=order.order_union.php", opt).request();
}
toggleOrderUnion = function (){
    jQuery('.order_union').toggle();
}