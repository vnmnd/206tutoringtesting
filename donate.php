<?php
require_once('config.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta charset="utf-8">
<title>Donate</title>
<style>
  .donate-process,
  .donate-thanks,
  .donate-alert {
    font-size: 1.2em;
    -webkit-transition: all .3s ease-out;
    -moz-transition: all .3s ease-out;
    -o-transition: all .3s ease-out;
    transition: all .3s ease-out;
    visibility: hidden;
    opacity: 0;
    height: 0;
    display: block;
  }
  .donate-process.show,
  .donate-thanks.show,
  .donate-alert.show {
    opacity: 1;
    height: auto;
    visibility: visible;
    padding: 1em;
  }
  .donate-alert.show {
    background: #f6cfcf;
  }
  .donate-thanks.show {
    background: #39d1b4;
    color: #fff;
  }
</style>
</head>

<body>

  <div id="main" role="main">

    <section>

      <span class="donate-alert" aria-expanded="false"></span>
      <span class="donate-process" aria-expanded="false">processing your donation...</span>
      <span class="donate-thanks" aria-expanded="false"></span>

      <h1>Donate</h1>
      <p><em>Enter an amount below, or use the quick links for a specific amount.</em></p>

      $ <input type="text" id="amt" value="">
      <button id="donateNow" type="submit">Donate</button>
    </section>

  </div>

<script src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
<script src="https://checkout.stripe.com/checkout.js"></script>

<script>
$(document).ready(function(){
  // scroll to top for processing
  function scrollTo() {
    var hash = '#main';
    var destination = $(hash).offset().top;
    stopAnimatedScroll();
    $('html, body').stop().animate({
      scrollTop: destination
    }, 400, function() { window.location.hash = hash; });
    return false;
  }
  function stopAnimatedScroll(){
    if ( $('*:animated').length > 0 ) {$('*:animated').stop();}
  }
  if(window.addEventListener) {
    document.addEventListener('DOMMouseScroll', stopAnimatedScroll, false);
  }
  document.onmousewheel = stopAnimatedScroll;

  // prevent decimal in donation input
  $('#amt').keypress(function(){
    preventDot(event)
  });

  function preventDot(event){
    var key = event.charCode ? event.charCode : event.keyCode;  
    if (key == 46){
      event.preventDefault();
      return false;
    }
  }

  function showProcessing() {
    scrollTo();
    $('.donate-process').addClass('show').attr('aria-expanded', 'true');
    $('.donate-thanks, .donate-alert').removeClass('show').attr('aria-expanded', 'false');
  }

  function hideProcessing() {
    $('.donate-process').removeClass('show').attr('aria-expanded', 'false');
  }

  // set up Stripe config, ajax post to charge
  var handler = StripeCheckout.configure({
    key: '<?php echo $stripe['publishable_key'] ?>',
    image: '/assets/home_page/spaceneedlelogo.jpg',
    closed: function(){document.getElementById('donateNow').removeAttribute('disabled');},
    token: function(token) {
      $.ajax({
        url: '/charge.php',
        type: 'POST',
        dataType: 'json',
        beforeSend: showProcessing,
        data: {
          stripeToken: token.id,
          stripeEmail: token.email,
          donationAmt: donationAmt
        },
        success: function(data) {
          hideProcessing();
          $('#amt').val('');
          if (data.error!='') {
            $('.donate-alert').addClass('show').text(data.error).attr('aria-expanded', 'true');
          } else {
            $('.donate-thanks').addClass('show').text(data.success).attr('aria-expanded', 'true');
          }
        },
        error: function(data) {
          $('.donate-alert').show().text(data).attr('aria-expanded', 'true');
        }
      });
    }
  });

  // donate now button, open Checkout
  $('#donateNow').click(function(e) {
    // strip non-numbers from amount and convert to cents
    donationAmt = document.getElementById('amt').value.replace(/\D/g,'') + '00';
    // make sure there is an amount
    if (donationAmt < 1) {
      $('#amt').val('').focus();
      e.preventDefault();
    } else {
      $('#donateNow').attr('disabled', 'disabled');
      // Open Checkout
      handler.open({
        name: '206 Tutoring',
        description: 'Payment',
        amount: donationAmt,
        billingAddress: true
      });
      e.preventDefault();
    }
  });

  // Close Checkout on page navigation
  $(window).on('popstate', function() {
    handler.close();
  });
});
</script>

</body>
</html>
