<?php

  /* mcm_html_footer.php - prints an html footer (yes, really) */
  
function mcm_html_footer($params) {
?>

    <hr>
    <?php if ($params) echo '<p id="last-visit">' . $params['user_name'] . ' &bull; last visit: ' . $params['last_visit'] . '</p>'; ?>
  </body>
</html>
<?php

}

?>
