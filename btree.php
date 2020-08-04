<?php
session_start();
$servername = "localhost:3306";
$username = "root";
$password = "password";              // change to your own password.

$conn = new mysqli($servername, $username, $password) or die(mysqli_error($conn));
// indicates the the maximum number of ptrs that a B+ tree treenode can point to is 3+1 = 4.
// build a b+ tree.
class TreeNode
{
    /*
      isleaf: indicates whether the treenode is a leaf node.
      key: an array of keys.
      size: the size of keys.
      ptr: an array of tree nodes from the next level.
      next: the node to the right. // I don't plan on using this attribute. Please ingore this my dear friends!!!
      par: parent node.
    */
    public $isleaf;
    public $key;
    public $size;
    public $value;
    public $ptr;
    public $next;
    public $par;

    public function __construct() {
        $this-> key = [];
        $this-> value = [];
        $this-> ptr = [];
        $this-> par = null;
    }

}
class Tree
{
  public $root; // the root node of our tree
  public $max = 3;
  public function __construct() {
    $this -> root = null;
    $this -> height = 0;
  }

  public function isEmpty() {
    return $this -> root === null;
  }

  public function insertinitial($val, $cursor, $child) {

  }

    // public function findparent($cursor, $child){
    //   $parent = new TreeNode();
    //   if ($cursor-> isleaf == true || ($cursor-> ptr[0])-> isleaf) {
    //     return null;
    //   }
    //   for ($i = 0; $i < $cursor-> size + 1; $i++) {
    //       if ($cursor-> ptr[$i] == $child) {
    //         $parent = $cursor;
    //         return $parent;
    //       } else {
    //         $parent = $this -> findParent($cursor-> ptr[$i], $child);
    //         if ($parent != null)
    //           return $parent;
    //       }
    //   }
    //   return $parent;
    // }

  public function search($val){
    if ($this -> root === null){
      // echo "No Records Present!"."<br>";
      return -1;
    }
    $cursor = $this -> root;

    while ($cursor -> isleaf == false){
      for ($i = 0; $i < $cursor -> size; $i++){
        if ($val < $cursor-> key[$i]) {
          $cursor = $cursor-> ptr[$i];
          break;
        }
        if ($i == ($cursor-> size) - 1) {
          $cursor = $cursor-> ptr[$i + 1];
          break;
        }
      }
    }
    for ($i = 0; $i < $cursor-> size; $i++) {
      if ($cursor-> key[$i] == $val) {
        // echo "Found $val"."<br>";
        return 1;
      }
    }
    // echo "$val Not Found"."<br>";
    return 0;
  }

  public function insert($val, $name){
    while($this -> search($val) == 1){
      $val += 0.01;
    }

    if ($this -> root === null){
      $this -> root = new TreeNode();
      $this -> root -> key[0] = $val;
      $this -> root -> value[0] = $name;
      $this -> root -> isleaf = true;
      $this -> root -> size = 1;
      $this -> height += 1;
      return;
    }
    $cursor = $this -> root;
    $parent = null;
    while ($cursor -> isleaf == false){
      $flag = false;
      for ($i = 0; $i < $cursor -> size; $i++){
        if ($cursor -> key[$i] > $val){
          $flag = true;
          $cursor = $cursor -> ptr[$i];
          break;
        }
      }
      if ($flag == false){
        $cursor = $cursor -> ptr[$cursor -> size];
      }
    }

    if ($cursor -> size < $this-> max){
      $temp = $cursor -> key[0];
      $i = 0;
      while ($val > $cursor -> key[$i] && $i < $cursor -> size){$i++; }
      for ($j = $cursor -> size; $j > $i; $j--){
        $cursor -> key[$j] = $cursor -> key[$j - 1];
        $cursor -> value[$j] = $cursor -> value[$j - 1];
      }
      $cursor -> key[$i] = $val;
      $cursor -> value[$i] = $name;
      $cursor -> size = $cursor -> size + 1;
      $cursor -> ptr[$cursor-> size] = $cursor -> ptr[$cursor-> size - 1];
      $cursor -> ptr[($cursor -> size - 1)] = null;
    }

    if ($cursor -> size == $this -> max){
      if ($this -> getroot() == $cursor){
        $newroot = new TreeNode();
        $mid = intval($this -> max / 2 );

        $newroot_id = $cursor -> key[$mid];
        $newroot_name = $cursor -> name[$mid];

        ($newroot -> key)[] = $newroot_id;
        ($newroot -> value)[] = $newroot_name;

        $newroot -> isleaf = false;
        $newroot -> size = 1;
        // Split on mid.
        $left = new TreeNode();
        $right = new TreeNode();
        $left -> isleaf = true;
        $right -> isleaf = true;

        for ($i = 0; $i < $mid; $i++){
          $left -> key[$i] = $cursor -> key[$i];
          $left -> value[$i] = $cursor -> value[$i];
          $left -> size = $left -> size + 1;
        }

        for ($i = $mid; $i < $cursor -> size; $i++){
          $right -> key[$i-$mid] = $cursor -> key[$i];
          $right -> value[$i-$mid] = $cursor -> value[$i];
          $right -> size = $right -> size + 1;
        }

        $left -> next = $right;

        ($newroot -> ptr)[] = $left;
        ($newroot -> ptr)[] = $right;

        $this -> root = $newroot;
        $this -> height ++;
        $left -> par = $newroot;
        $right -> par = $newroot;
        unset($cursor);
      }else {
        $done = false;
        $mid = intval($this -> max/ 2);
        $new_id = $cursor -> key[$mid];
        $new_name = $cursor -> value[$mid];
        $marker = -1;
        for ($i = 0; $i < $cursor -> size; $i++){
          if ($cursor -> par -> key[$i] > $new_id){
            $done = true;
            $marker = $i;
            break;
          }
        }

        if ($done == false){
          ($cursor -> par -> key)[] = $new_id;
          ($cursor -> par -> value)[] = $new_name;
          $original_size = $cursor -> par -> size;
          $cursor -> par -> size = $cursor -> par -> size + 1;
          // split by mid.
          $left = new TreeNode();
          $right = new TreeNode();
          $left -> isleaf = true;
          $right -> isleaf = true;
          for ($i = 0; $i < $mid; $i++){
            $left -> key[$i] = $cursor -> key[$i];
            $left -> value[$i] = $cursor -> value[$i];
            $left -> size = $left -> size + 1;
          }

          for ($i = $mid; $i < $cursor -> size; $i++){
            $right -> key[$i-$mid] = $cursor -> key[$i];
            $right -> value[$i-$mid] = $cursor -> value[$i];
            $right -> size = $right -> size + 1;
          }

          $cursor -> par -> ptr[$original_size] = $left;
          $cursor -> par -> ptr[$original_size + 1] = $right;
          $left -> par = $cursor -> par;
          $right -> par = $cursor -> par;
          unset($cursor);
          if ($left -> par -> size == $this -> max){
            $this -> adjust($left -> par);
          }
        }else {
          // Done.
          // Shuffle the keys to the right.
          for ($i = $cursor -> par -> size; $i > $marker; $i--){
            $cursor -> par -> key[$i] =  $cursor -> par -> key[$i-1];
            $cursor -> par -> value[$i] =  $cursor -> par -> value[$i-1];
          }
          $cursor -> par -> key[$marker] = $new_id;
          $cursor -> par -> value[$marker] = $new_name;
          $cursor -> par -> size = $cursor -> par -> size + 1;
          for ($i = $cursor -> par -> size + 1; $i > $marker + 1; $i--){
            $cursor -> par -> ptr[$i] = $cursor -> par -> ptr[$i-1];
          }
          $left = new TreeNode();
          $right = new TreeNode();

          for ($i = 0; $i < $mid; $i++){
            $left -> key[$i] = $cursor -> key[$i];
            $left -> value[$i] = $cursor -> value[$i];
            $left -> size = $left -> size + 1;
          }

          for ($i = $mid; $i < $cursor -> size; $i++){
            $right -> key[$i-$mid] = $cursor -> key[$i];
            $right -> value[$i-$mid] = $cursor -> value[$i];
            $right -> size = $right -> size + 1;
          }

          $left -> isleaf = true;
          $right -> isleaf = true;

          //if($val == 0)print($cursor -> par -> key[2]);

          $cursor -> par -> ptr[$marker] = $left;
          $cursor -> par -> ptr[$marker + 1] = $right;
          $left -> par = $cursor -> par;
          $right -> par = $cursor -> par;
          unset($cursor);
          if ($left -> par -> size == $this -> max){
            $this -> adjust($left -> par);
          }
        }
      }
      return;
    }
  }

  public function adjust($cursor){
    /* To do adjust */
    /* This function handles the situation where both the key and its parent needs to be modified. */
    // print($cursor -> key[0]);

    if($cursor-> size < $this -> max)return;
    if ($cursor -> par == null){
      $newroot = new TreeNode();
      $mid = intval($this -> max / 2 );
      //$ptr_mid = intval(($this -> max + 1) / 2 );
      $ptr_mid = $mid + 1;
      $newroot_id = $cursor -> key[$mid];
      $newroot_name = $cursor -> value[$mid];
      ($newroot -> key)[] = $newroot_id;
      ($newroot -> value)[] = $newroot_name;
      $newroot -> isleaf = false;
      $newroot -> size = 1;
      // Split on mid.
      $left = new TreeNode();
      $right = new TreeNode();
      $left -> isleaf = false;
      $right -> isleaf = false;

      for ($i = 0; $i < $mid; $i++){
        $left -> key[$i] = $cursor -> key[$i];
        $left -> value[$i] = $cursor -> value[$i];
        $left -> size = $left -> size + 1;
      }
      for ($i = 0; $i < $ptr_mid; $i++){
        $left -> ptr[$i] = $cursor -> ptr[$i];
        $left -> ptr[$i] -> par = $left;
      }

      for ($i = $mid + 1; $i < $cursor -> size; $i++){
        $right -> key[$i-$mid - 1] = $cursor -> key[$i];
        $right -> value[$i-$mid - 1] = $cursor -> value[$i];
        $right -> size = $right -> size + 1;
      }
      for ($i = $ptr_mid; $i <= $cursor -> size; $i++){
        $right -> ptr[$i-$ptr_mid] = $cursor -> ptr[$i];
        $right -> ptr[$i-$ptr_mid] -> par = $right;
      }

      $left -> next = $right;

      $this -> root = $newroot;
      $this -> height++;

      //if($this -> height == 4)print($cursor -> key[1]);
      ($newroot -> ptr)[] = $left;
      ($newroot -> ptr)[] = $right;

      $left -> par = $newroot;
      $right -> par = $newroot;
      unset($cursor);
    }else{
      $newroot = $cursor -> par;
      $mid = intval($this -> max / 2 );
      $ptr_mid = $mid + 1;

      for($i = 0; $i < $newroot -> size; $i++){
        if($newroot -> key[$i] > $cursor -> key[$mid] )break;
      }
      for($j = $newroot -> size; $j > $i; $j--){
        $newroot -> key[$j] = $cursor -> par -> key[$j - 1];
        $newroot -> value[$j] = $cursor -> par -> value[$j - 1];
      }
      // for($b = 0; $b < 3; $b++){
      //   if($this -> height == 3)print($cursor-> key[$b]);
      // }


      for($j = $newroot -> size + 1; $j > $i + 1; $j--){
        $newroot -> ptr[$j] = $newroot -> ptr[$j - 1];
      }


      $newroot-> key[$i] = $cursor -> key[$mid];
      $newroot-> value[$i] = $cursor -> value[$mid];
      $tmp = $i;
      $newroot -> size++;

      $left = new TreeNode();
      $right = new TreeNode();
      $left -> isleaf = false;
      $right -> isleaf = false;

      for ($i = 0; $i < $mid; $i++){
        $left -> key[$i] = $cursor -> key[$i];
        $left -> value[$i] = $cursor -> value[$i];
        $left -> size = $left -> size + 1;
      }
      for ($i = 0; $i < $ptr_mid; $i++){
        $left -> ptr[$i] = $cursor -> ptr[$i];
        $left -> ptr[$i] -> par = $left;
      }

      for ($i = $mid + 1; $i < $cursor -> size; $i++){
        $right -> key[$i- $mid - 1] = $cursor -> key[$i];
        $right -> value[$i- $mid - 1] = $cursor -> value[$i];
        $right -> size = $right -> size + 1;
      }
      for ($i = $ptr_mid; $i <= $cursor -> size; $i++){
        $right -> ptr[$i-$ptr_mid] = $cursor -> ptr[$i];
        $right -> ptr[$i-$ptr_mid] -> par = $right;
      }
      $left -> par = $newroot;
      $right -> par = $newroot;

      $left -> next = $right;
      $newroot -> ptr[$tmp] = $left;
      $newroot -> ptr[$tmp + 1] = $right;

      unset($cursor);

      if($newroot -> size == $this -> max)$this -> adjust($newroot);
    }
    return;
  }

  public function level_order_traversal(){
    for($i = 1; $i <= $this -> height; $i++){
      $this -> show($this -> root, $i);
      echo "|<br>";
    }
    return;
  }
  public function show($cursor, $level){

    if($cursor == NULL)return;
    if($level == 1){
      echo " || ";
      for ($i = 0; $i < $cursor-> size; $i++) {
        echo '[';
        echo $cursor -> key[$i]." ";
        echo $cursor -> value[$i]." ";
        echo ']';
      }
    }else{
      for($i = 0; $i < $cursor-> size + 1; $i++){
        $this -> show($cursor -> ptr[$i], $level - 1);
      }
    }
  }

  public function show2($cursor, $level, $val_start, $val_end){

    if($cursor == NULL)return;
    if($level == 1){
      // echo " | ";
      for ($i = 0; $i < $cursor-> size; $i++) {
        if($cursor -> key[$i] >= $val_start && $cursor -> key[$i] <= $val_end){
          echo $cursor -> key[$i]." ";
          echo $cursor -> value[$i]." ";
        }
      }
    }else{
      for($i = 0; $i < $cursor-> size + 1; $i++){
        $this -> show2($cursor -> ptr[$i], $level - 1, $val_start, $val_end);
      }
    }
  }

  public function getroot(){
     return $this -> root;
  }

  public function search_range($val_start, $val_end){
    // To do.
    $this-> show2($this -> root, $this -> height, $val_start, $val_end);
  }

  public function search_range_ret($val_start, $val_end){
    // To do.
    $result = [];
    $this-> show_ret($this -> root, $this -> height, $val_start, $val_end, $result);
    return $result;
  }

  public function show_ret($cursor, $level, $val_start, $val_end, &$result){
    if($cursor == NULL)return;
    if($level == 1){
      // echo " | ";
      for ($i = 0; $i < $cursor-> size; $i++) {
        if($cursor -> key[$i] >= $val_start && $cursor -> key[$i] <= $val_end){
          $result[strval($cursor -> key[$i])] = $cursor -> value[$i];
          //echo $cursor -> key[$i];
        }
      }
    }else{
      for($i = 0; $i < $cursor-> size + 1; $i++){
        $this -> show_ret($cursor -> ptr[$i], $level - 1, $val_start, $val_end, $result);
      }
    }
  }
}

// $conn = new mysqli($servername, $username, $password) or die(mysqli_error($conn));

$result = $conn->query("SELECT * FROM projectDatabase.auth_user WHERE last_login = (SELECT MAX(last_login) FROM projectDatabase.auth_user)") or die($conn->error);

$row = $result->fetch_array();
$username = $row['username'];
$res = $conn->query("SELECT * FROM projectDatabase.LikeBook where username = '$username'") or die($conn->error);
$all_similar_books = array();
$all_friends_author_books = [];
while($row = $res->fetch_assoc()) {
  $tmp = $row['bookId'];
  $author = $conn-> query("SELECT author from projectDatabase.books where book_id = '$tmp'") or die($conn->error);
  while ($au_row = $author-> fetch_assoc()) {
    $name = $au_row['author'];
    $friends = $conn-> query("SELECT author_id_1 as au_id from projectDatabase.friendsauthor where author_id_2 = '$name'
    union
    SELECT author_id_2 as au_id from projectDatabase.friendsauthor where author_id_1 = '$name'") or die($conn->error);
    while ($fri = $friends->fetch_assoc()) {
      $fri_name = $fri['au_id'];
      $fri_book = $conn->query("SELECT book_id from projectDatabase.books where author = '$fri_name' and rating > 4") or die($conn->error);
      while ($bok = $fri_book->fetch_assoc()) {
        $all_friends_author_books[] = $bok['book_id'];
      }
    }
  }
  // print('tmp');
  // echo $tmp.'<br>';
  // print('459');
  $sim_1 = $conn-> query("SELECT book_id_2 from projectDatabase.similarbooks WHERE book_id_1 = '$tmp'") or die($conn->error);
  // print(gettype($sim_1->fetch_array()));
  // print('461');
  $sim_2 = $conn-> query("SELECT book_id_1 from projectDatabase.similarbooks WHERE book_id_2 = '$tmp'") or die($conn->error);
  $sim_3 = [];
  $sim_4 = [];
  while ($row1 = $sim_1-> fetch_assoc()) {
    $tmp1 = $row1['book_id_2'];
    $all_similar_books[] = $tmp1;
    // $sim_3_tmp = $conn-> query("SELECT book_id_2 from projectDatabase.similarbooks WHERE book_id_1 = '$tmp1'") or die($conn->error);
    // // print(gettype($sim_3_tmp->fetch_array()));
    // while ($tmprow = $sim_3_tmp->fetch_assoc()) {
    // // if (gettype($sim_3_tmp->fetch_array()) == 'array') {
    //   // print(gettype((array)$sim_3));
    //   // print('778');
    //   $tmptmp = $tmprow['book_id_2'];
    //   // echo $tmptmp.'<br>';
    //   $all_similar_books[] = $tmptmp;
    //   // $sim_3 = array_merge((array) $sim_3, $sim_3_tmp-> fetch_array());
    // }
    // $sim_3_tmp = $conn-> query("SELECT book_id_1 from projectDatabase.similarbooks WHERE book_id_2 = '$tmp1'") or die($conn->error);
    // while ($tmprow = $sim_3_tmp->fetch_assoc()) {
    // // if (gettype($sim_3_tmp->fetch_array()) == 'array') {
    //   // print(gettype((array)$sim_3));
    //   // print('778');
    //   $tmptmp = $tmprow['book_id_1'];
    //   // echo $tmptmp.'<br>';
    //   $all_similar_books[] = $tmptmp;
    //   // $sim_3 = array_merge((array) $sim_3, $sim_3_tmp-> fetch_array());
    // }
  }

  while ($row2 = $sim_2-> fetch_assoc()) {
    $tmp2 = $row2['bookId'];
    $all_similar_books[] = $tmp2;
    $sim_4_tmp = $conn-> query("SELECT book_id_2 from projectDatabase.similarbooks WHERE book_id_1 = '$tmp2'") or die($conn->error);
    // while ($tmprow = $sim_4_tmp->fetch_assoc()) {
    // // if (gettype($sim_3_tmp->fetch_array()) == 'array') {
    //   // print(gettype((array)$sim_3));
    //   // print('778');
    //   $tmptmp = $tmprow['book_id_2'];
    //   // echo $tmptmp.'<br>';
    //   $all_similar_books[] = $tmptmp;
    //   // $sim_3 = array_merge((array) $sim_3, $sim_3_tmp-> fetch_array());
    // }
    // $sim_4_tmp = $conn-> query("SELECT book_id_1 from projectDatabase.similarbooks WHERE book_id_2 = '$tmp2'") or die($conn->error);
    // while ($tmprow = $sim_4_tmp->fetch_assoc()) {
    // // if (gettype($sim_3_tmp->fetch_array()) == 'array') {
    //   // print(gettype((array)$sim_3));
    //   // print('778');
    //   $tmptmp = $tmprow['book_id_1'];
    //   // echo $tmptmp.'<br>';
    //   $all_similar_books[] = $tmptmp;
    //   // $sim_3 = array_merge((array) $sim_3, $sim_3_tmp-> fetch_array());
    // }
  }
  // $all_similar_books = array_merge((array)$all_similar_books, (array)$sim_1, (array)$sim_2, (array)$sim_3, (array)$sim_4);
}

$bookcnt = [];
foreach ($all_similar_books as &$value) {
  if ($value == NULL) continue;
  if ($bookcnt[$value] == NULL) {
    $bookcnt[$value] = 1;
  } else {
    $bookcnt[$value] += 1;
  }
}

foreach ($all_friends_author_books as &$value) {
  if ($value == NULL) continue;
  if ($bookcnt[$value] == NULL) {
    $bookcnt[$value] = 1;
  } else {
    $bookcnt[$value] += 1;
  }
}
$tree = new Tree();
$cnt = 0;
$total_score = 0;
foreach ($bookcnt as $key => &$value) {
  $tmpres = $conn-> query("SELECT * from projectDatabase.books where book_id = '$key'") or die($conn->error);
  $tmprow = $tmpres->fetch_assoc();
  $rat = $tmprow['rating'];
  $rat_cnt = $tmprow['rating_cnt'];
  $re_cnt = $tmprow['review_cnt'];
  $book_score = 0.4 * $rat + 0.4 * $value + 0.1 * $rat_cnt/10000 + 0.1 * $re_cnt / 1000;
  $tree -> insert($book_score, $key);
  $cnt += 1;
  $total_score += $book_score;
}
if ($cnt == 0){
  $avg = 10000000;
}
else $avg = $total_score/$cnt;
$select = 0;
foreach ($bookcnt as $key => &$value) {
  $tmpres = $conn-> query("SELECT * from projectDatabase.books where book_id = '$key'") or die($conn->error);
  $tmprow = $tmpres->fetch_assoc();
  $rat = $tmprow['rating'];
  $rat_cnt = $tmprow['rating_cnt'];
  $re_cnt = $tmprow['review_cnt'];
  $book_score = 0.4 * $rat + 0.4 * $value + 0.1 * $rat_cnt/10000 + 0.1 * $re_cnt / 1000;
  if($book_score > $avg * 1.8){
    $select++;
  }
}
// echo $select;

$result = $tree -> search_range_ret(1.8 * $avg, 10000);

?>

<html>
<head>
  <title>Bookworm | A Comprehensive Book Recommendation Service For Everyone</title>
  <link rel = "icon" href = "1200px-Closed_Book_Icon.svg.png"
type = "image/x-icon">
  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">

<style>
  .left {
  position: absolute;
  left: 400px;
  }
  .right {
  position: absolute;
  right: 400px;
  }
  .custom {
  height: 60px;
  }
</style>
</head>

<body>
  <div class="topnav" style = "background-color: #333;
overflow: hidden; position: sticky; top: 0; width:100%;">
    <a class="active" href="index.php" style = "float: left;
      color: #f2f2f2;
      text-align: center; padding: 14px 16px;
      text-decoration: none;
      font-size: 17px;">Book</a>
    <a href="authorindex.php" style = "float: left;
      color: #f2f2f2;
      text-align: center; padding: 14px 16px;
      text-decoration: none;
      font-size: 17px;">Author</a>
    <a href="btree.php" style = "float: left;
      color: #f2f2f2;
      text-align: center; padding: 14px 16px;
      text-decoration: none;
      font-size: 17px; background-color: #4CAF50;">Recommendation</a>
    <a style = "float: right;
      color: #f2f2f2;
      text-align: center; padding: 14px 16px;
      text-decoration: none;
      font-size: 17px;"> Hello, User!</a>
</div>

<?php
if(isset($_SESSION['message'])):?>

<div class = "alert alert-<?=$_SESSION['msg_type']?>">

  <?php
    echo $_SESSION['message'];
    unset($_SESSION['message']);
  ?>
</div>
<?php endif;?>

<div class = "container">
    <div class = "row justify-content-center">
      <table class = "table">
        <thead>
           <tr>
            <th>book_id</th>
            <th>name</th>
            <th>author</th>
            <th>rating</th>
            <th>rating_cnt</th>
            <th>review_cnt</th>
           </tr>
        </thead>
        <?php
          foreach ($result as $key => &$value){
            $full_res = $conn -> query("SELECT * from projectdatabase.books where book_id = '$value'");
            while($row = $full_res->fetch_assoc()): $count++;?>
        <tr>
          <td><?php echo $row['book_id'] ?></td>
          <td><?php echo $row['name'] ?></td>
          <td><?php echo $row['author'] ?></td>
          <td><?php echo $row['rating'] ?></td>
          <td><?php echo $row['rating_cnt'] ?></td>
          <td><?php echo $row['review_cnt'] ?></td>
        </tr>
        <?php endwhile;}?>
      </table>
    </div>
  </div>
</body>
</html>
