<!-- include header of page -->
<?php $this->load->view("includes/header");?>

<body>
<!-- insert navigation bar for page -->
<?php $url=uri_string(); $this->load->view("includes/navbar.php",array("url"=>$url)); ?>

<!--insert contain of page -->
<?php echo $container; ?>

<!-- insert footer of page -->
<?php $this->load->view("includes/footer");?>
