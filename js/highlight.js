(function($, window, undefined) {

  // Add pretty print to all pre and code tags.
  $('pre, code').addClass("prettyprint");

  // Remove prettify from code tags inside pre tags.
  $('pre code').removeClass("prettyprint");

  // Activate pretty presentation.
  prettyPrint();
})(jQuery, this);