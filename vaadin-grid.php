<?php
require("mysql.php");
class VaadinGrid
{
    private $columns, $headers;
    private $id;
    public static function imports()
    {
        echo '<script src="https://cdn.vaadin.com/vaadin-core-elements/preview/webcomponentsjs/webcomponents-lite.min.js"></script>';
        echo '<link href="https://cdn.vaadin.com/vaadin-core-elements/preview/vaadin-grid/vaadin-grid.html" rel="import">';
    }
    function __construct($table, $order, $columns = "")
    {
        $this->table = $table;
        if ($columns == "") {
            $columns = array();
            $q       = "DESC $table";
            $r       = mysql_query($q);
            while ($row = mysql_fetch_row($r)) {
                $columns[] = $row[0];
            }
        }
        $this->columns = $columns;
        $this->order   = $order;
        session_start();
        $this->id = 0;
        while (isset($_SESSION["vaadin-grid"][$this->id])) {
            $this->id++;
        }
        $_SESSION["vaadin-grid"][$this->id] = $this;
    }
    function setHeader($column, $header)
    {
        $this->headers[$column] = $header;
    }
    function display()
    {
        echo "<vaadin-grid id=\"grid{$this->id}\">";
        // Columns
        for ($i = 0; $i < count($this->columns); $i++) {
            echo "<vaadin-grid-column>";
            $col    = $this->columns[$i];
            $header = $col;
            if (isset($this->headers[$col])) {
                $header = $this->headers[$col];
            }
            echo "<template class='header'>$header</template>";
            echo "<template>[[item.{$col}]]</template>";
            echo "</vaadin-grid-column>";
        }
        echo "</vaadin-grid>";
        echo "<script>";
        $grid = "grid{$this->id}";
        echo "$grid.size=";
        $countarr = mysql_fetch_row(mysql_query("SELECT COUNT(*) FROM {$this->table} ORDER BY {$this->order}"));
        echo $countarr[0];
        echo ";\n";
        echo "$grid.data=";
        echo json_encode($this->getData(0, 50));
        echo ";";
        echo "$grid.dataSource = ";
?>
function(params, callback) {
   var fetch = function(first, last, data, callback) {
       console.log("Fetching "+first+"-"+last);
       var url = "vaadin-grid.php?vaadin-grid-data=<?
        echo $this->id;
?>&offset="+first+"&size=" + (last-first+1);
       var xhr = new XMLHttpRequest();
       xhr.onreadystatechange = function() {
         if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
           var response = JSON.parse(xhr.responseText);
           data.push.apply(data, response);
           callback();
         }
       }
    xhr.open('GET', url, true);
    xhr.send();
   };

   var firstRequested = params.pageSize * params.page;
   var lastRequested = firstRequested + params.pageSize - 1;
   var grid = this;
   var lastAvailable = grid.data.length-1;
   if (lastRequested > lastAvailable) {
     fetch(lastAvailable+1, lastRequested, grid.data, function() {
        callback(grid.data.slice(firstRequested, firstRequested + params.pageSize));
     });
   } else {
     callback(grid.data.slice(firstRequested, firstRequested + params.pageSize));
   }
};
<?php
        echo "</script>";
    }
    function getData($start, $size)
    {
        $q    = "SELECT * FROM {$this->table} ORDER BY {$this->order} LIMIT $start,$size";
        $r    = mysql_query($q);
        $data = array();
        while ($row = mysql_fetch_assoc($r)) {
            $rowdata = array();
            foreach ($this->columns as $col) {
                $rowdata[$col] = $row[$col];
            }
            $data[] = $rowdata;
        }
        return $data;
    }
}
// Serve data for lazy loading
if ($_GET["vaadin-grid-data"]) {
    session_start();
    $grid = $_SESSION["vaadin-grid"][$_GET["vaadin-grid-data"]];
    if (isset($grid)) {
        $start = $_GET["offset"];
        $limit = $_GET["size"];
        if ($start == intval($start) && $limit == intval($limit)) {
            echo json_encode($grid->getData($start, $limit));
        }
    }
}
?>
