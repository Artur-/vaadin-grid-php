<?php
require("vaadin-grid.php");
?>
<!doctype html>
<html>
  <head>
  <?
VaadinGrid::imports();
?>
  </head>
<body>
<?php
$mysqlTable = "person";
$orderBy    = "id";
$columns    = array(
    "id",
    "firstName",
    "lastName"
);
$grid       = new VaadinGrid($mysqlTable, $orderBy, $columns);
$grid->setHeader("id", "Id");
$grid->setHeader("firstName", "First Name");
$grid->setHeader("lastName", "Last Name");
$grid->display();
?>
</p>
</body>
</html>
