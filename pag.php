<?php
/**
 * Created by PhpStorm.
 * User: salman
 * Date: 1/14/2019
 * Time: 10:14 PM
 */

$con = mysqli_connect("localhost", "root", "", "denmark1");

function pagination($con,$table,$pno,$n)
{
    $sql = $con->query("SELECT COUNT(*) as rows FROM wh_textdata");
    $rows = mysqli_fetch_assoc($sql);
    //$totalnumofrecord = 10000;
    $pageno = $pno;

    $numberOfRecordsPerPage = $n;

    $last = ceil($rows['rows']/$numberOfRecordsPerPage);

    $pagination ="";

    if ($last != 1) {

        if ($pageno > 1) {

            $previous = "";

            $previous = $pageno - 1;

//            $pagination .= "<li class='page-item'><a class='page-link' pn='".$previous."' href='#' style='color:#333;'> Previous </a></li>";
            $pagination = $pagination."<a href=pagination.php?pageno=$previous style='color: #333333'>Previous</a>";
        }

        for ($i = $pageno - 5; $i < $pageno; $i++) {/// current page sa phela kitna page display hn gya
            if ($i > 0)
            $pagination = $pagination."<a href=pagination.php?pageno=$i>" . $i . "</a>";
        }

        $pagination .= "<a href=pagination.php?pageno=$pageno style='color: #333333'>$pageno</a>";//current Page display kara ga

        for ($i = $pageno +1; $i <= $last; $i++){
            $pagination .= "<a href=pagination.php?pageno=$i>$i</a>";
            if ($i > $pageno + 4)//hm current page sa aga 5 page dsplay krna chata ha
                break;
        }
        if ($last > $pageno){//This will display next Page
            $next = $pageno+1;
            $pagination .= "<a href=pagination.php?pageno=$next>Next</a>";
        }
    }
    $limit = "LIMIT ".($pageno - 1) * $numberOfRecordsPerPage.",".$numberOfRecordsPerPage;//yaha pa hm limit define kr raha ha


    return ["pagination"=>$pagination,"limit"=>$limit];


}
if(isset($_GET['pageno'])){
    $pageno = $_GET['pageno'];

    $table = "wh_textdata";

    $array = pagination($con, $table, $pageno,10);// ya ak array return kara ge

    $sql = "SELECT * FROM ".$table." ".$array["limit"];

    $result = $con->query($sql) or die($con->error);

    while ($row = mysqli_fetch_assoc($result)){
        echo "<div style='margin: 0 auto; font-size: 20px;'><b>".$row['id']."</b> ".$row['title']."</div>";
    }
    echo $array["pagination"];

    print_r(pagination($con, "xx", $pageno,10));
}