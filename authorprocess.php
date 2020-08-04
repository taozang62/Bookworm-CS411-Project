<?php
session_start();
$servername = "localhost:3306";
$username = "root";
$password = "password";              // change to your own password.


$author_id = 0;
$name = '';
$rating = '';
$rating_cnt = '';
$update = false;
$search_name = '';
$input = '';
$old_name = '';
// Create connection
$conn = new mysqli($servername, $username, $password) or die(mysqli_error($conn));

if ( isset($_POST['search'])) {

  $input = $_REQUEST['search_name'];
  $result = $conn->query("SELECT count(*) as cnt FROM projectDatabase.author where name like '%$input%'") or  die(mysqli_error($conn));
  $row = $result -> fetch_array();
  $cnt = $row['cnt'];
  if ($cnt == 1){
    $_SESSION['message'] = "1 record found!";
    $_SESSION['msg_type'] = "success";
    $_SESSION['input'] = "$input";
  }
  else if ($cnt == 0){
    $_SESSION['message'] = "No records found!";
    $_SESSION['msg_type'] = "danger";
    $_SESSION['input'] = "$input";
  }

  else {
    $_SESSION['message'] = "$cnt records found!";
    $_SESSION['msg_type'] = "success";
    $_SESSION['input'] = "$input";
  }
 header("location: authorsearch.php");
}
if ( isset($_POST['back'])) {
  header("location: authorindex.php");
}
if ( isset( $_POST['save']) ){
    // Get 3 required fields from form.
    $au_name = $_REQUEST['name'];
    $rating = $_REQUEST['rating'];
    if (!empty($au_name) && !empty($rating)){

      $sql = "SELECT EXISTS(SELECT name FROM projectDatabase.author WHERE name = '$au_name') AS num";
      $res = mysqli_query($conn, $sql) or die(mysqli_error($conn));
      $row = mysqli_fetch_array($res, MYSQLI_ASSOC);
      $flag = $row['num'];

      $sql = "SELECT MAX(author_id) as maximum FROM projectDatabase.author";
      $res = mysqli_query($conn, $sql) or die(mysqli_error($conn));
      $row = mysqli_fetch_array($res, MYSQLI_ASSOC);
      $new_id = -1;
      $new_rating = -1;
      $new_rating_cnt = -1;
      if ($flag == 0){
        $new_id = $row['maximum'] + 1;
        $new_rating = $rating;
        $new_rating_cnt = 1;
      }
      else {
        $sql = "SELECT author_id, name, rating, rating_cnt FROM projectDatabase.author WHERE name = '$au_name'";
        $res = mysqli_query($conn, $sql) or die(mysqli_error($conn));
        $row = mysqli_fetch_array($res, MYSQLI_ASSOC);
        $new_id = $row['author_id'];
        $old_name = $row['name'];
        $new_rating = $row['rating'];
        $new_rating_cnt = $row['rating_cnt'];
        $new_rating = ($new_rating * $new_rating_cnt + $rating) / ($new_rating_cnt + 1);
        $new_rating_cnt = $new_rating_cnt + 1;
      }
      // echo "id:".$new_id."<br>";
      // echo "new rating: ".$new_rating."<br>";
      // echo "new rating count: ".$new_rating_cnt."<br>";

      $sql = "SELECT EXISTS(SELECT name FROM projectDatabase.author WHERE name = '$au_name') AS num";
      $res = mysqli_query($conn, $sql) or die(mysqli_error($conn));
      $row = mysqli_fetch_array($res, MYSQLI_ASSOC);
      $b_flag = $row['num'];

      $sql = "SELECT MAX(author_id) as maximum FROM projectDatabase.author";
      $res = mysqli_query($conn, $sql) or die(mysqli_error($conn));
      $row = mysqli_fetch_array($res, MYSQLI_ASSOC);
      $new_b_id = -1;
      $new_b_rating = -1;
      $new_b_rating_cnt = -1;

      if ($b_flag == 0){
        $new_b_id = $row['maximum'] + 1;
        $new_b_rating = $rating;
        $new_b_rating_cnt = 1;
      }
      else {
        $sql = "SELECT author_id, rating, rating_cnt FROM projectDatabase.author WHERE name = '$au_name'";
        $res = mysqli_query($conn, $sql) or die(mysqli_error($conn));
        $row = mysqli_fetch_array($res, MYSQLI_ASSOC);
        $new_b_id = $row['author_id'];
        $new_b_rating = $row['rating'];
        $new_b_rating_cnt = $row['rating_cnt'];
        $new_b_rating = ($new_b_rating * $new_b_rating_cnt + $rating) / ($new_b_rating_cnt + 1);
        $new_b_rating_cnt = $new_b_rating_cnt + 1;
      }

      // we have new_b_id, new_b_rating, new_b_rating_cnt, new_id, new_rating, new_rating_cnt, book_name, author_name and rating.
      // prepare to update or insert.
      if ($flag == 0){
        $sql = "INSERT INTO projectDatabase.author VALUES('$new_id', '$au_name', '$new_rating', '$new_rating_cnt')";
        $res = mysqli_query($conn, $sql) or die(mysqli_error($conn));
      }
      else{
        $sql = "UPDATE projectDatabase.author SET name = '$au_name', rating = '$new_rating', rating_cnt = '$new_rating_cnt' WHERE author_id = '$new_id'";
        $res = mysqli_query($conn, $sql) or die(mysqli_error($conn));
        $sql = "UPDATE projectDatabase.books set author = '$au_name' where author = '$old_name'";
      }

      // if ($b_flag == 0){
      //   $sql = "INSERT INTO projectDatabase.books VALUES('$new_b_id', '$book_name', '$au_name', '$new_b_rating', '$new_b_rating_cnt', 0)";
      //   $res = mysqli_query($conn, $sql) or die(mysqli_error($conn));
      // }
      // else {
      //   $sql = "UPDATE projectDatabase.books SET rating = '$new_b_rating', rating_cnt = '$new_b_rating_cnt' WHERE book_id = '$new_b_id'";
      //   $res = mysqli_query($conn, $sql) or die(mysqli_error($conn));
      // }

    //   echo "rows affected!"."<br>";
    //   $sql = "SELECT * FROM projectDatabase.author where author_id = '$new_id'";
    //   $res = mysqli_query($conn, $sql) or die(mysqli_error($conn));
    //   $row = mysqli_fetch_array($res, MYSQLI_ASSOC);
    //   echo $row['author_id']." ".$row['name']." ".$row['rating']." ".$row['rating_cnt']."<br>";
    //   $sql = "SELECT * FROM projectDatabase.books where book_id = '$new_b_id'";
    //   $res = mysqli_query($conn, $sql) or die(mysqli_error($conn));
    //   $row = mysqli_fetch_array($res, MYSQLI_ASSOC);
    //   echo $row['book_id']." ".$row['name']." ".$row['author']." ".$row['rating']." ".$row['rating_cnt']." ".$row['review_cnt']."<br>";

      $_SESSION['message'] = "Record has been saved!";
      $_SESSION['msg_type'] = "success";
      header("location: authorindex.php");
    }

    else {
      $_SESSION['message'] = "Input empty! ";
      $_SESSION['msg_type'] = "danger";
      header("location: authorindex.php");
    }
  }

  if ( isset( $_GET['delete'])){
    $id = $_GET['delete'];
    $result = $conn->query("SELECT name as au FROM projectDatabase.author WHERE author_id = $id") or die($conn->error);
    $row = $result->fetch_array();
    $author_name = $row['au'];
    $result = $conn -> query("SELECT author_id as aid FROM projectDatabase.author WHERE name = '$author_name'") or die($conn->error);
    $row = $result->fetch_array();
    $aid = $row['aid'];
    $conn->query("DELETE FROM projectDatabase.author WHERE author_id = $id") or die($conn->error);
    $result = $conn->query("DELETE from projectDatabase.books WHERE author = '$author_name'");
    // $row = $result->fetch_array();
    // $cnt = $row['cnt'];
    // if ($cnt == 0){
    //   $conn->query("DELETE FROM projectDatabase.author WHERE author_id = $aid") or die($conn->error);
    // }
    $_SESSION['message'] = "Author has been deleted!";
    $_SESSION['msg_type'] = "danger";
    header("location: authorindex.php");
  }
//update not working
  if ( isset( $_GET['edit'])){
    $id = $_GET['edit'];
    $update = true;
    $result = $conn->query("SELECT * FROM projectDatabase.author WHERE author_id = $id") or die($conn->error);
    // print($id);
    if(!empty($result)){
        $row = $result->fetch_array();
        $author_id = $row['author_id'];
        // print($book_id);
        $name = $row['name'];
        $old_name = $row['name'];
        $rating = $row['rating'];
        $rating_cnt = $row['rating_cnt'];
        // print($old_name);
        $_SESSION['old_name'] = $old_name;
    }
  }

  if ( isset( $_POST['update'])){
    // print('here');
    $old_name = $_SESSION['old_name'];
    // print($old_name);
    $author_id = $_POST['author_id'];
    $name = $_POST['name'];
    $rating = $_POST['rating'];
    $conn->query("UPDATE projectDatabase.author SET name = '$name', rating = '$rating' WHERE author_id = '$author_id'") or die($conn->error);
    $conn->query("UPDATE projectDatabase.books SET author = '$name' WHERE author = '$old_name'") or die($conn->error);
    $_SESSION['message'] = "Record has been updated!";
    $_SESSION['msg_type'] = "warning";
    header("location: authorindex.php");
  }

  // if ( isset( $_GET['like'])){
  //   // choose current user
  //   header("location: index.php");
  //   $id = $_GET['like'];
  //   // print($id);
  //   $result = $conn->query("SELECT * FROM projectDatabase.auth_user WHERE last_login = (SELECT MAX(last_login) FROM projectDatabase.auth_user)") or die($conn->error);
  //   if(count($result) == 1){
  //       $row = $result->fetch_array();
  //       $username = $row['username'];
  //       $tmp = $conn->query("SELECT * FROM projectDatabase.LikeBook where username = '$username' and bookId = '$id'") or die($conn->error);
  //       $row_cnt = $tmp->num_rows;
  //       if ($row_cnt == 0) {
  //         $conn->query("INSERT INTO projectDatabase.LikeBook VALUES ('$username','$id')") or die($conn->error);
  //       }
  //   }
  //   print($username);
  //   $_SESSION['message'] = "Thank you";
  //   $_SESSION['msg_type'] = "success";
  //
  // }
