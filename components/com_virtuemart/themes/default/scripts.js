function updateCart(el) {
    jQuery(function ($) {
        // Сам элемент
        var e = $(el);
        // Количество товара этой позиции
        var quant = e.val();

        if (quant > 0 && form_input_is_int(quant)) {
            // Его родитель
            var p = e.parent().parent();
            // ID позиции
            var pid = p.find('[name="product_id"]').attr('value');
            // Ячейка со стоимостью
            var current = e.closest('tr').find('.subtotal');
            var currentprice = e.closest('tr').find('.price');
            // Общая стоимсоть
            var total = $("#total");

            // Отправляем JSON-запрос
            $.getJSON(p.attr('action'),
                {
                    option: "com_virtuemart",
                    quantity: quant,
                    vmxtsrch: 1,
                    page: "shop.cart",
                    func: "cartUpdate",
                    product_id: pid,
                    prod_id: pid,
                    Itemid: 1
                },
                function (data) {
                    // Распаковываем вернувшийся объект JSON
                    // и подставляем значения в соответствующие поля
                    current.html(data.current);
                    currentprice.html(data.product_price);
                    total.html(data.total);

                    if ($('#tw').size()) {
                        var stateAttr = ((jQuery('input[name=addressType]:checked').val() == 'primary') ? 'state' : 'sc_state');
                        var needCalc = true;
                        if (jQuery('#' + stateAttr).val() == 77) {
                            if ($('#tw').val() < 12 && data.weight < 12)
                                needCalc = false;

                            if ($('#tw').val() > 12 && data.weight > 12)
                                needCalc = true;
                        }
                        $('#tw').val(data.weight);
                        displayShippingWithW(needCalc);
                    }

                    if ($('#coupon_value').size()) {
                        $('#coupon_value').html(data.coupon);
                    }

                    if (jQuery('.cart-quantity-' + pid).size()) {
                        if (parseInt(jQuery('.in-stock-' + pid).html()) < quant) {
                            jQuery('.in-cart-' + pid).html(quant);
                            jQuery('.cart-quantity-' + pid).show();
                            jQuery('.cart-quantity-' + pid).addClass('cart-product-out-of-stock-of-cart');
                        } else {
                            jQuery('.cart-quantity-' + pid).hide();
                            jQuery('.cart-quantity-' + pid).removeClass('cart-product-out-of-stock-of-cart');
                        }
                    }
                    jQuery(document).trigger('checkYaPaymentAvalible');
                });
        }
    });
}

function deleteCart(el) {
    jQuery(function ($) {
        // Сам элемент
        var e = $(el);

        // Его родитель
        var p = e.parent().parent();
        // ID позиции
        var pid = p.find('[name="product_id"]').attr('value');
        // Общая стоимсоть
        var total = $("#total");

        // Отправляем JSON-запрос
        $.getJSON(p.attr('action'),
            {
                option: "com_virtuemart",
                vmxtsrch: 1,
                page: "shop.cart",
                func: "cartDelete",
                product_id: pid,
                prod_id: pid,
                Itemid: 1
            },
            function (data) {
                // Распаковываем вернувшийся объект JSON
                // и подставляем значения в соответствующие поля
                e.parents('tr').remove();
                total.html(data.total);

                if (parseInt(data.total) == 0) {
                    window.location.href = "/";
                    return;
                }


                if ($('#tw').size()) {
                    var stateAttr = ((jQuery('input[name=addressType]:checked').val() == 'primary') ? 'state' : 'sc_state');
                    var needCalc = true;
                    if (jQuery('#' + stateAttr).val() == 77) {
                        if ($('#tw').val() < 12 && data.weight < 12)
                            needCalc = false;

                        if ($('#tw').val() > 12 && data.weight > 12)
                            needCalc = true;
                    }
                    $('#tw').val(data.weight);
                    displayShippingWithW(needCalc);
                }

                if ($('#coupon_value').size()) {
                    $('#coupon_value').html(data.coupon);
                }

                // может скрыть лейблы
                if (jQuery('.cart-product-out-of-stock').size() == 0) {
                    jQuery('.alert-error-noall').hide();
                    jQuery('.alert-error-noone').hide();
                }

                if (jQuery('.btn[name=delete]').size() == jQuery('.cart-product-out-of-stock').size() && jQuery('.alert-error-noall').size() != 1) {
                    // О, счастливчик!
                    jQuery('.alert-error-noone').replaceWith("<div class='alert alert-error alert-error-noall'><h3>Внимание!</h3><p>В Вашей корзине присутствуют товары, отмеченные ярлыком <span class='label out-of-stock'>Нет в наличии</span>. Это означает, что товара действительно <strong>нет в наличии</strong>. Ваш заказ будет помещен в статус \"Обрабатывается\" на время ожидания товаров. Сроки ожидания могут составлять до нескольких месяцев.</p></div>").effect('highlight');
                    jQuery('.alert-error-noall').effect('highlight');
                }
                jQuery(document).trigger('checkYaPaymentAvalible');
            });
    })
}


function activeCupon(el) {
    jQuery(function ($) {
        // Сам элемент
        var e = $(el);

        // Его родитель
        var p = e.parent().parent();
        // ID позиции
        var pid = p.find('[name="product_id"]').attr('value');
        // Общая стоимсоть
        var total = $("#total");

        jQuery.ajax({
            url: '/cart/checkout?redirected=1&option=com_virtuemart&vmxtsrch=1&page=checkout.index&coupon_code=' + $('#coupon_code').val() + '&do_coupon=yes&Itemid=1',
            success: function (resp) {
                if ($(resp).find('.coupon_ok').size()) {
                    e.parents('#coupon_div').remove();
                    $('#tr-row-container').show();
                    $('#coupon_value').html($(resp).find('.coupon_ok').html());
                    $('#tr-row-coupon').show();
                    calcFullPrice();
                }
                else
                    $('#cupon_message').html($(resp).html());
            }
        });

    })
}

/**
 *    Функция проверки целочисленности
 **/
function form_input_is_int(input) {
    return !isNaN(input) && parseInt(input) == input;
}


jQuery(function ($) {

    // отывы
    if (jQuery('.table-item-opinions tr').size() > 10) {
        jQuery('.table-item-opinions tr:gt(9)').hide();

        var next = jQuery('.table-item-opinions tr:hidden').size();
        if (next > 10)
            next = 10;

        if (next > 0)
            jQuery('.table-item-opinions tr:last').after("<tr><td colspan=\"2\" class=\"show_more_opinion_td\"><a href=\"#\" class=\"show_more_opinion\">Показать ещё " + next + "</a></td></tr>");
    }

    jQuery(document).on('click', '.show_more_opinion', function (e) {
        jQuery('.table-item-opinions tr:hidden:lt(10)').show();
        jQuery(this).parent().parent().remove();

        var next = jQuery('.table-item-opinions tr:hidden').size();
        if (next > 10)
            next = 10;

        if (next > 0)
            jQuery('.table-item-opinions tr:last').after("<tr><td colspan=\"2\" class=\"show_more_opinion_td\"><a href=\"#\" class=\"show_more_opinion\">Показать ещё " + next + "</a></td></tr>");

        e.preventDefault();
    });

    // поиск
    jQuery('#keyword').autocomplete({
        source: function (request, response) {
            this.source = function (request, response) {
                if (this.xhr) {
                    this.xhr.abort();
                }
                var keyword = request.term;
                request = {};
                request.keyword = keyword;
                request.output = 'pdf';
                request.limit = 20;
                request.only_page = 1;
                request.ajax_request = 1;
                var url = '/list-all-products';
                this.xhr = $.ajax({
                    url: url,
                    data: request,
                    dataType: "json",
                    success: function (data) {
                        response(data);
                    },
                    error: function () {
                        response([]);
                    }
                });
            };

        },
        position: {
            my: "right top",
            at: "right bottom",
        },
        minLength: 3, // Минимальная длина запроса для срабатывания автозаполнения
        select: function (event, ui) {
            var item = ui.item;
            window.location = item.product_flypage;
        }
    });

    $.ui.autocomplete.prototype._renderItem = function (ul, item) {

        var labeltext = $(item.label).find('.itemtitle').text();
        var reg = new RegExp(this.term, "igm");
        var labeltext2 = labeltext.replace(reg, "<span class='searchwordi'>" +
        this.term +
        "</span>");

        var t = item.label.replace(labeltext, labeltext2);

        item.label = jQuery("<div/>").html(item.label).text();
        item.value = jQuery("<div/>").html(item.value).text();

        return $("<li></li>")
            .data("item.autocomplete", item)
            .append(t)
            .appendTo(ul);
    };
});


// AJAX FUNCTIONS 
function loadAddCartAttr(el, url) {

    var callback = {
        success: function (responseText) {
            if (el == 'new') {
                if (document.boxB) {
                    document.boxB.close();
                    clearTimeout(timeoutID);
                }
            }

            var $html = jQuery(responseText);
            var addcart = $html.find('.addcart').html();
            var prodsku = $html.find('.prodsku').html();
            var title = $html.find('h1').html();
            var pricetable = $html.find('.pricetable').html();
            var pic = $html.find('.pic').html();
            addcart = addcart.replace('loadNewPage', 'loadAddCartAttr');

            var html = '<div class="addtocart_multi_modal_cont">';
            html += '<div class="row-fluid">';
            html += '<div class="span4">';
            html += '<div class="pic">' + pic + '</div>';
            html += '</div>';
            html += '<div class="span8">';
            html += '<p class="prodsku">' + prodsku + '</p>';
            html += '<h1>' + title + '</h1>';
            html += '<div class="pricetable">' + pricetable + '</div>';
            html += '<div class="addcart">' + addcart + '</div>';
            html += '</div>';
            html += '</div>';
            html += '</div>';

            if (el == 'new') {
                document.boxB = new MooPrompt(notice_lbl, html, {
                    buttons: 0,
                    width: 600,
                    height: 220,
                    overlay: true
                });
            } else {
                jQuery('.addtocart_multi_modal_cont').parent().html(html);
            }
            setTimeout(function () {
                jQuery(document).trigger('cbxAutoheight');
            }, 100);
            setTimeout(function () {
                jQuery(document).trigger('cbxAutoheight');
            }, 200);
            setTimeout(function () {
                jQuery(document).trigger('cbxAutoheight');
            }, 300);
            setTimeout(function () {
                jQuery(document).trigger('cbxAutoheight');
            }, 500);
        }
    }

    var opt = {
        method: 'get',
        onComplete: callback.success
    }
    new Ajax(url + '&only_page=1', opt).request();
}

jQuery(document).on('openmodaladdcart', function (event, $el) {
    var url = $el.attr('href');
    var el = 'new';
    loadAddCartAttr(el, url)
});

jQuery(document).on('click', '.openmodaladdcart', function (e) {
    var $el = jQuery(this);
    jQuery(document).trigger('openmodaladdcart', [$el]);
    e.preventDefault();
});

// зададим обработчик с двумя дополнительными параметрами
jQuery(document).on('cbxAutoheight', function (event, param1, param2) {
    var height = jQuery('.cbContainer .cbContent').height();
//    jQuery('.cbContainer').height(height);
    jQuery('.cbContainer').css('height', 'auto');
    jQuery('.cbContainer .cbBox').css('height', 'auto');
});

//var fio = document.getElementById('first_name_field');
//alert('dad');
jQuery(document).keyup(function () {
    //   alert('DSD');
});
/*
 jQuery(document).on('keyup','#first_name_field',function(){
 var data = {data: jQuery('#first_name_field').value};
 var opt = {
 // Use POST
 method: 'post',
 // Send this lovely data
 data: data,
 // Handle successful response
 onComplete: function (response) {
 alert('good');
 },
 evalScripts: true
 }
 new Ajax("/index.php?option=com_virtuemart&page=order.ajax_autocomplete.php", opt).request();
 });
 */
/*
 jQuery(document).on('keyup','#first_name_field',function() {

 jQuery("#first_name_field").suggestions({
 serviceUrl: "https://dadata.ru/api/v2",
 token: "b33ae07263350921a8399dd9fc4f87f034448b92",
 type: "NAME",
 count: 5,

 onSelect: function(suggestion) {
 console.log(suggestion);
 }
 });

 });*/
jQuery(document).ready(function () {
    jQuery("#first_name_field").suggestions({
        serviceUrl: "https://dadata.ru/api/v2",
        token: "b33ae07263350921a8399dd9fc4f87f034448b92",
        type: "NAME",
        count: 5,

        onSelect: function (suggestion) {
            console.log(suggestion);
        }
    });

    jQuery("#email_field").suggestions({
        serviceUrl: "https://dadata.ru/api/v2",
        token: "b33ae07263350921a8399dd9fc4f87f034448b92",
        type: "EMAIL",
        count: 5,

        onSelect: function (suggestion) {
            console.log(suggestion);
        }
    });

    jQuery("#address_1_field").suggestions({
        serviceUrl: "https://dadata.ru/api/v2",
        token: "b33ae07263350921a8399dd9fc4f87f034448b92",
        type: "ADDRESS",
        count: 5,

        onSelect: function (suggestion) {
            console.log(suggestion);

            geo_lat = suggestion.data.geo_lat;
            geo_lon = suggestion.data.geo_lon;
            region_with_type = suggestion.data.region_with_type;
            region = suggestion.data.region;
            postal_code = suggestion.data.postal_code;

            jQuery('#zip_field').val(postal_code);

            console.log(jQuery('#state option').length);
            jQuery('#state option').each(function () {
                if (this.text.indexOf(region) != -1) {
                    jQuery("#state").val(this.value);
                }
            });

            var data = {geo_lat: geo_lat, geo_lon: geo_lon};
            jQuery.ajax({
                url: '/components/com_virtuemart/checkout.ajax.php',
                data: data,
                success: function (resp) {
                    alert(resp);
                }
            });
        }
    });
});
change_region = function () {
    jQuery.ajax({
        url: '/components/com_virtuemart/get_region_list.ajax.php',
        success: function (resp) {
            var region = JSON.parse(resp)[0];
            var country = JSON.parse(resp)[1]
            var region_list = '<div style="float:left;"><strong><h5>Страна</h5></strong>';

            for (i = 0; i < country.length; i++) {
                region_list += '<a style="width: 300px !important;" id="button_country' + i + '" class="country_buttons buttons" name="' + country[i] + '" onclick="ChangeLabelCountry(name);">' + country[i] + '</a><br>';
            }
            region_list += "</div>";

            region_list += '<div id="div_region" style="float:right;"<strong><h5>Регион</h5></strong>';
            for (i = 0; i < region.length; i++) {
                region_list += '<a style="width: 300px !important;" id="button_region' + i + '" class="region_buttons" name="' + region[i] + '" onclick="ChangeLabelState(name);">' + region[i] + '</a><br>';
            }
            region_list += "</div>";

            document.boxB = new MooPrompt('Укажите ваше местоположение', region_list, {
                buttons: 0,
                width: 530,
                height: 600,
                overlay: false
            });
        }
    });
}
ChangeLabelCountry = function (value) {
    //document.getElementById('state_label').val(id);
    if (value == 'Российская Федерация') {
        //jQuery('.region_buttons, #div_region').show();
        //jQuery('#country_label').html('Россия, ');
    } else {
        jQuery('#country_label').html(value);
        jQuery('#state_label').html('');
        jQuery('.your_region').hide();
        document.boxB.close();

        var data = {country: value};
        jQuery.ajax({
            url: '/components/com_virtuemart/session.ajax.php',
            data: data,
            success: function (resp) {
              //  alert(resp);
            }
        });
    }
}
ChangeLabelState = function (value) {
    //document.getElementById('state_label').val(id);
    //jQuery('#country_label').html('Россия, ');
    jQuery('#country_label').html('');
    jQuery('#state_label').html(value);
    jQuery('.your_region').show();
    document.boxB.close();
    var data = {state: value};
    jQuery.ajax({
        url: '/components/com_virtuemart/session.ajax.php',
        data: data,
        success: function (resp) {
           // alert(resp);
        }
    });
}

