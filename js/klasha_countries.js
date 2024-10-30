
jQuery(function ($) {

  var states_json = my_js.country.replace(/&quot;/g, '"'),
    states = $.parseJSON(states_json);

  // Resetting state on country change
  $("#billing_country").change(function (e) {
    var country = e.target.value
    $(':input#billing_state').empty()
    var options = '',
      state = states[ country ];

    $('#billing_state').remove();
      if(state && state.length !== 0) {
        $('#billing_state_field span.woocommerce-input-wrapper').append($("<select name=\"billing_state\" id=\"billing_state\" />"))
        for( var index in state ) {
          if ( state.hasOwnProperty( index ) ) {
            $(':input#billing_state').append($("<option />").val(index).text( state[ index ]))
            options = options + '<option value="' + index + '">' + state[ index ] + '</option>';
          }
        }
      } else {
        $('#billing_state_field span.woocommerce-input-wrapper').append($("<input name=\"billing_state\" id=\"billing_state\" class=\"input-text \" type=\"text\"/>"))
      }
  })
});
