<?php
wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . '../admin/css/Maps_percents.css', array(), $this->version, 'all');
wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . '../admin/js/Maps_percents.js', array('jquery'), rand(111, 9999), false);

function wpdocs_register_my_custom_menu_page() {
  add_menu_page(
          __('Maps percents', 'textdomain'),
          'Maps percents',
          'manage_options',
          'custompage',
          'my_custom_menu_page',
          'dashicons-building',
          90
  );
}

add_action('admin_menu', 'wpdocs_register_my_custom_menu_page');

/* ---------------SALVA IN DB ----------------- */

function salva() {
  global $wpdb;
  $perc = $_POST['perc'];

  $percx = $perc[0];
  $percy = $perc[1];
  $percw = $perc[2];
  $perch = $perc[3];

  global $wpdb;
  $table = $wpdb->prefix . 'percents';
  $data = array('percx' => $percx,
      'percy' => $percy,
      'percw' => $percw,
      'perch' => $perch);
  $format = array('%s', '%f');
  $wpdb->insert($table, $data, $format);
  $my_id = $wpdb->insert_id;
  echo $perc;
  wp_die();
}

add_action('wp_ajax_salva', 'salva');
/* ---------------------------------------------------- */

/**
 * Display a custom menu page
 */
function my_custom_menu_page() {

  /* ---- salva immaggine ------------ */

  function test() {
    $uploaddir = plugin_dir_path(__FILE__) . 'img/';
    $uploadfile = $uploaddir . basename($_FILES['fileToUpload']['name']);
    if (move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $uploadfile)) {
      
    } else {
      
    }
    $fd = fopen($uploadfile, 'r');
    while (($row = fgetcsv($fd)) !== FALSE) {
      $data[] = $row;
    }
    fclose($fd);
  }

  add_action('wp_admin_test', 'test');
  /* ----------------------------------------------------- */
  ?>
  <style>

    <?php
    echo '#hotel{';
    if (isset($_POST['selimag'])) {
      echo 'background-image:url("' . plugin_dir_url(__FILE__) . 'img/' . $_POST['selimag'] . '");}';
    }else{
      echo "background-image: url('http://www.bristolhotel-odessa.com/wp-content/uploads/2015/06/1-hotel-front.jpg');}";
    }
    ?>

  </style>

  <div id="wrap">
    <div id="hotel">

    </div>
    <div id="ui">

      <div id="savebox">
        <div id="name">
          <input id="accmname" type="text" value="Room 1">
        </div>
        <div id="buttons">
          <input id="clear" class="finish" type="button" value="Clear Selection">
          <input id="save" class="finish" type="button" value="Save Accommodation">
        </div>
        <form action="" method="post" enctype="multipart/form-data" id='upload'>
          Seleziona l'immagine da salvare:
          <br />
          <input type="file" name="fileToUpload" id="fileToUpload"><br />
          <input type="submit" value="Upload file" name="submit" onclick='<?php test(); ?>' form="upload">
        </form>
        <ul id="accmlist">

        </ul>
        <?php

        function getDirContents($dir, &$results = array()) {
          $files = scandir($dir);
          foreach ($files as $key => $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
            if (!is_dir($path)) {
              $results[] = $path;
            } else if ($value != "." && $value != "..") {
              getDirContents($path, $results);
              $results[] = $path;
            }
          }
          return $results;
        }
        echo "<p>scegli l'immagine:</p>";
        echo '<form action="" method="post" id="immagine">';
        echo '<select name="selimag" form="immagine">';
        $cont = 0;
        foreach (getDirContents(plugin_dir_path(__FILE__) . 'img/') as $immagine) {
          echo '<option value="' . $file = substr($immagine, strrpos($immagine, '/') + 1) . '" id="' . $cont . '">'
          . $file = substr($immagine, strrpos($immagine, '/') + 1) .
          '</option>';
        }
        echo '</select>';
        echo '<input type="submit" name="subimg" form="immagine" value="applica"></form>'
        ?>

      </div>
    </div>
  </div>

  <h4>
    Just drag and draw rectangles above the picture and save
    them as available rooms.
  </h4>
  <?php
}