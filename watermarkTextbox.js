//Based on hinttextbox by Drew Noakes
//http://www.drewnoakes.com/code/javascript/hintTextbox.html

var watermarkClass = "watermarkTextbox";
var watermarkActiveClass = "watermarkTextboxActive";
 
function initWatermarkTextboxes() {
  //Ensure this is only initialized once.  
  if (!$.data(this, "watermarkAPI")) {
    $.data(this, "watermarkAPI", "initialized");

    //Inject CSS styling for the textboxes
    var newCSS = '<style type="text/css">' +
                 'INPUT.watermarkTextbox { color: #888; }' +
                 'INPUT.watermarkTextboxActive { color: #000; }' +
                 '</style>';
    $("head").append(newCSS);

    //Hook up the eventing for the textboxes
    $('input[type="text"],[type="password"],[className*="'+watermarkClass+'"]').each( 
      function() {
        $(this).data('hintText', $(this).val());
        $(this).focus(function () { handleWatermarkTextboxFocus(this); });
        $(this).blur(function () { handleWatermarkTextboxBlur(this); });
      });
  }
}

function handleWatermarkTextboxFocus(textbox) {
  if ($(textbox).hasClass(watermarkClass)) {
    $(textbox).removeClass(watermarkClass).addClass(watermarkActiveClass).val('');
  }
}

function handleWatermarkTextboxBlur(textbox) {
  if ($(textbox).val().length == 0) {
    $(textbox).val($(textbox).data('hintText'));
    $(textbox).removeClass(watermarkActiveClass).addClass(watermarkClass);
  }
}
    
