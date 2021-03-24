/*
* Stupid simple popup notification
*/
function SimplePopup(message, type) {
  var ifrm = document.createElement("div");
  ifrm.classList.add("wp-scholar-popup", type);
  ifrm.innerHTML = message;
  document.body.appendChild(ifrm);
  setTimeout(function() {
    document.body.removeChild(ifrm)
  }, 2500);
};


/*
* CSS hover pseudo-class sucks here, the anchor links disappear
* too fast if we put then outside of the heading CSS bounding box
* So we redo a hover event from scratch here
*/
headings = document.querySelectorAll("h2, h3, h4, h5, h6");

headings.forEach(function(test) {
  var anchor = test.getElementsByClassName("anchor-link")[0];
  var toc = test.getElementsByClassName("toc-link")[0];
  if(!anchor || !toc) return;

  anchor.addEventListener("click", function( event ) {
    var textArea = document.createElement("textarea");
    textArea.value = document.location.href.match(/(^[^#]*)/)[0]+anchor.getAttribute("href");
    document.body.appendChild(textArea);
    textArea.select();
    try {
      document.execCommand('copy');
      SimplePopup(wp_scholar_translation.success_message, 'info');
    } catch (err) {
      SimplePopup(wp_scholar_translation.failure_message, 'error');
    }
      document.body.removeChild(textArea);
  }, true);

  test.addEventListener("mouseenter", function() {
    anchor.classList.add("active");
    toc.classList.add("active");
  }, true);

  test.addEventListener("mouseleave", function() {
    setTimeout(function() {
      anchor.classList.remove("active");
      toc.classList.remove("active");
    }, 2000);
  }, false);
});


/*
* Hover card for footnotes
*/
Array.from(document.getElementsByClassName("footnote-ref")).forEach(
  function (element, index, array) {

    element.addEventListener("mouseenter", function () {
      // close previously opened popup so we have only one at a time
      var popup = document.getElementById("footnote-popup");
      if (popup) document.body.removeChild(popup);
      document.getElementById("main").style.filter = "brightness(95%)";

      // create new popup
      var link_id = element.getAttribute("href").replace("#", "");
      var footnote = document.getElementById(link_id).innerHTML;
      var ifrm = document.createElement("div");
      ifrm.classList.add("footnote-popup");
      ifrm.id = "footnote-popup";
      ifrm.innerHTML = footnote;

      // position the new popup next to the link original position
      var viewportOffset = element.getBoundingClientRect();
      var width = viewportOffset.width;
      var height = viewportOffset.height;
      var x = viewportOffset.x;
      var y = viewportOffset.y;
      var win_height = window.innerHeight;
      var win_width = window.innerWidth;

      // basically, we split the viewport in 4 rectangles sharing the original link at a corner
      // then we put the card in the largest rectangle and hope we will have enough surface there
      if(x > win_width / 2.)
        ifrm.style.right = win_width - x + "px";
      else
        ifrm.style.left = x + width + "px";

      if(y > win_height / 2.)
        ifrm.style.bottom = win_height - y + "px";
      else
        ifrm.style.top = y + height + "px";

      document.body.appendChild(ifrm);
    }, false);

    element.addEventListener("mouseleave", function () {
      // destroy the card after 1 seconds when cursor leaves the area
      setTimeout(function () {
        document.getElementById("main").style.filter = "none";
        var popup = document.getElementById("footnote-popup");
        if(popup) document.body.removeChild(popup);
      }, 1000);
    }, false);
  }
);
