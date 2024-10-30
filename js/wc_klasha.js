//console.log("I was called now");
jQuery(function ($) {
  var checkout_form = $("form.checkout")
  
  // Resetting state on country change
  // $("#billing_country").change(function(){
  //   console.log('aaa')
  //     $( ':input.country_to_state' ).change();
  // })


  let isPaymentSuccessful = false;
  
  option = $("input[name='payment_method']:checked").val();

  if (option == 'klasha') {
    // $('#place_order').hide();
    $('.payment_box.payment_method_klasha').show();
  } else {
    // $('#place_order').show();
    $('.payment_box.payment_method_klasha').hide();
  }

  $("input[name='payment_method']").change(function() {
    option = $("input[name='payment_method']:checked").val();

    if (option == 'klasha') {
      // $('#place_order').hide();
      $('.payment_box.payment_method_klasha').show();
    } else {
      // $('#place_order').show();
      $('.payment_box.payment_method_klasha').hide();
    }

  });
  
  const getCurrency = async(billingCountry) => {
    var currency
     const result = await $.getJSON("https://klasha-public-assets.s3.eu-west-3.amazonaws.com/countries.json");
      const { country: countries }  = result.countries
      const countryPayload = countries.filter( country => {
        return  country.countryCode.toLowerCase() === billingCountry.toLowerCase()
       })
      const [ { currencyCode }] = countryPayload
      currency = currencyCode
      return currency
  }

 

  var tokenRequest = async function  () {
    // event.preventDefault();

  };
  var cbUrl  = wc_klasha_params.cb_url;

  function callWhenDone(data) {
    if (data["status"] === "successful") {
      console.log(data)
      redirectPost(cbUrl,data);
      isPaymentSuccessful = true;
      //console.log("Is Payment Successful", isPaymentSuccessful)

      // $("#pstatus").text("Your payment is successful");
      // $("#pot").html(
      //   "<div style='font-weight:bold; color: #21c91c; font-size:18px'>Payment Complete, Complete your order below...</div>"
      // );
      // myAdminNotice(
      //   "Your payment is successful, You can complete your order below"
      // );
      console.log(data["tx_ref"])
      //console.log("DATA+++ ", data)
      // checkout_form.find("#txnRef").val(data["tx_ref"]);
      // //console.log(checkout_form.find("#txnRef").val());
      // $("#place_order").show();
      // $("#place_order").html("Complete Order");
      //
      // $("#place_order").click();

    } else {
      $("#pot").html(
          "<div style='font-weight:bold; color: #FF0000; font-size:18px'>Your payment failed please try again</div>"
      );

      // myAdminNotice(
      //   "Your payment was not successful, please try again!!"
      // );
    }

    // add a token to our hidden input field
    // console.log(data) to find the token
    //checkout_form.find('#misha_token').val(data.token);

    // deactivate the tokenRequest function event
    // checkout_form.off( 'checkout_place_order', tokenRequest );

    // submit the form now
    // checkout_form.submit();
    //console.log("I got here");
  }

  function makeid(length) {
    var result = "";
    var characters =
      "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    var charactersLength = characters.length;
    for (var i = 0; i < length; i++) {
      result += characters.charAt(Math.floor(Math.random() * charactersLength));
    }
    return result;
  }

  $("#klasha-payment-button").click( async function (event) {


    // if (option === 'klasha') {
    //   if ($("#place_order").text() !== "Complete Orde++r" && (isPaymentSuccessful === false)) {
        event.preventDefault();
       /* if (checkout_form.find("#k-login-test").val() === "block") {
          alert("please you have to login to proceed");
          return false;
        }*/
        // const billingCountry = checkout_form.find("#billing_country").val();
        const billingCountry = wc_klasha_params.country;
        //console.log("Billing Country ", billingCountry)
        const currency = await getCurrency(billingCountry)
        var kit = {
          currency: currency,
          callback: wc_klasha_params.callback,
          phone: wc_klasha_params.phone,
          email: wc_klasha_params.email,
          fullname: wc_klasha_params.firstname + " " + wc_klasha_params.lastname,
          tx_ref: wc_klasha_params.txnref,
          paymentType: "woo",
          callBack: callWhenDone,
        };

        var client = new KlashaClient(
            wc_klasha_params.merchantKey.toString(),
            wc_klasha_params.businessId.toString(),
            wc_klasha_params.amount,
            wc_klasha_params.containerId,
            wc_klasha_params.callback,
            currency,
            wc_klasha_params.currency,
            kit,
            wc_klasha_params.testmode
        );
        //console.log(kit);
        /*
                jQuery( function($) {
                    $("form.woocommerce-checkout")
                        .on('submit', function() { client.init(); } );
                } );*/
        // client.init();
    //   } else {
    //     console.log("klasha not choosen");
    //   }
    // }
   

  });

  //redirect function
  var redirectPost = function(location, args){
    // console.log(args);
    var form = '';
    jQuery.each( args, function( key, value ) {
      // value = value.split('"').join('\"')
      form += '<input type="hidden" name="'+key+'" value="'+value+'">';
    });
    jQuery('<form action="' + location + '" method="POST">' + form + '</form>').appendTo(jQuery(document.body)).submit();
  }


  // $('[data-action="place_order"]').click(async function (event) {
  //
  //   if ($('[data-action="place_order"]').text().toLowerCase() !== "complete order" && (isPaymentSuccessful === false)) {
  //     event.preventDefault();
  //     console.log("Multi step checkout")
  //     if (option === 'klasha') {
  //       if ($("#place_order").text().toLowerCase() !== "complete order" && (isPaymentSuccessful === false)) {
  //         event.preventDefault();
  //         const billingCountry = checkout_form.find("#billing_country").val();
  //         //console.log("Billing Country ", billingCountry)
  //         const currency = await getCurrency(billingCountry)
  //         var kit = {
  //           currency: currency,
  //           callback: wc_klasha_params.callback,
  //           phone: checkout_form.find("#billing_phone").val(),
  //           email: checkout_form.find("#billing_email").val(),
  //           fullname:
  //               checkout_form.find("#billing_first_name").val() +
  //               " " +
  //               checkout_form.find("#billing_last_name").val(),
  //
  //           tx_ref: makeid(10),
  //           paymentType: "woo",
  //           callBack: callWhenDone,
  //         };
  //
  //         var client = new KlashaClient(
  //             wc_klasha_params.merchantKey.toString(),
  //             wc_klasha_params.businessId.toString(),
  //             wc_klasha_params.amount,
  //             wc_klasha_params.containerId,
  //             wc_klasha_params.callback,
  //             currency,
  //             wc_klasha_params.currency,
  //             kit
  //         );
  //         //console.log(kit);
  //         /*
  //                 jQuery( function($) {
  //                     $("form.woocommerce-checkout")
  //                         .on('submit', function() { client.init(); } );
  //                 } );*/
  //         client.init();
  //       } else {
  //         console.log("klasha not choosen");
  //       }
  //     }
  //   } else {
  //     console.log("failed");
  //   }
  // });

  function makeid(length) {
    var result = "";
    var characters =
      "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    var charactersLength = characters.length;
    for (var i = 0; i < length; i++) {
      result += characters.charAt(Math.floor(Math.random() * charactersLength));
    }
    return result;
  }

  /**
   * Create and show a dismissible admin notice
   */
  function myAdminNotice(msg) {
    /* create notice div */

    var div = document.createElement("div");
    div.classList.add("notice", "notice-info");

    /* create paragraph element to hold message */

    var p = document.createElement("p");

    /* Add message text */

    p.appendChild(document.createTextNode(msg));

    // Optionally add a link here

    /* Add the whole message to notice div */

    div.appendChild(p);

    /* Create Dismiss icon */

    var b = document.createElement("button");
    b.setAttribute("type", "button");
    b.classList.add("notice-dismiss");

    /* Add screen reader text to Dismiss icon */

    var bSpan = document.createElement("span");
    bSpan.classList.add("screen-reader-text");
    bSpan.appendChild(document.createTextNode("Dismiss this notice"));
    b.appendChild(bSpan);

    /* Add Dismiss icon to notice */

    div.appendChild(b);

    /* Insert notice after the first h1 */
    
    // var h1 = document.getElementsByTagName("h1")[0];
    // h1.parentNode.insertBefore(div, h1.nextSibling);

    // what happens if there is no h1
    // document.body.append(div);

    document.querySelector(".woocommerce-info").appendChild(div);
    /* Make the notice dismissable when the Dismiss icon is clicked */

    b.addEventListener("click", function () {
      div.parentNode.removeChild(div);
    });
  }
});
