</div>
<?php
$skimresources = 1;
if (isset($skimresources))
{
?>
<script type="text/javascript" src="//s.skimresources.com/js/109663X1567436.skimlinks.js"></script>
<?php
}
else
{
?>
<script type="text/javascript">
  var vglnk = { key: '149486e901bcc4e535da2eed73200a97' };

  (function(d, t) {
    var s = d.createElement(t); s.type = 'text/javascript'; s.async = true;
    s.src = '//cdn.viglink.com/api/vglnk.js';
    var r = d.getElementsByTagName(t)[0]; r.parentNode.insertBefore(s, r);
  }(document, 'script'));
</script>
<?php
}
?>

<footer class="footer">
  <div class="well well-sm">
    <ul class="nav nav-pills container">
        <li><a href="/dealblog/index.php/about/">About</a></li>
        <li><a href="/dealblog/">Blog</a></li>
    </ul>
  </div>
</footer>

<script type="text/javascript">
  $(function() {
    // Handler for .ready() called.
    // OnClick handler for the "select all" checkbox on the stores sidebar
    $("#select-all-stores").on("click", function() {
      let isSelectAllChecked = document.getElementById("select-all-stores").checked;

      $('#stores-panel :checkbox:enabled').prop('checked', isSelectAllChecked);
    });
  });
</script>

</body>
</html>

