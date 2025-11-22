<?php
   $sbsp_promotional_header = SBSP_PLUGIN_DIR . 'includes/templates/headers/sbsp-promotional-header.php';
?>

<style>
    .mother-container {
      display: flex;
      flex-direction: column;
      gap: 10px;
      margin: 10px 10px 10px 0px;
    }
    .main-container {
      background-color: #ffffff;
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); /* optional shadow */
      max-width: 100%;
    }
</style>

<div class="mother-container">
  <?php require_once $sbsp_promotional_header; ?>
  <div class="main-container">
  