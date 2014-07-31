(function() {
  for (var name in { "nav": 1, "section": 1, "article": 1, "header": 1, "aside": 1, "hgroup": 1 })
    document.createElement(name);
})();