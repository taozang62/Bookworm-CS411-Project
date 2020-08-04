<?php session_start();?>
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
  overflow: hidden; position: sticky; top: 0; width: 100%;">
        <a class="active" href="index.php" style = "float: left;
          color: #f2f2f2;
          text-align: center; padding: 14px 16px;
          text-decoration: none;
          font-size: 17px;">Book</a>
        <a href="authorindex.php" style = "float: left;
          color: #f2f2f2;
          text-align: center; padding: 14px 16px;
          text-decoration: none;
          font-size: 17px; background-color: #4CAF50;">Author</a>
        <a href="btree.php" style = "float: left;
          color: #f2f2f2;
          text-align: center; padding: 14px 16px;
          text-decoration: none;
          font-size: 17px;">Recommendation</a>
        <a style = "float: right;
          color: #f2f2f2;
          text-align: center; padding: 14px 16px;
          text-decoration: none;
          font-size: 17px;"> Hello, User!</a>
        <a href="#bottom" style = "float: left;
            color: #f2f2f2;
            text-align: center; padding: 14px 16px;
            text-decoration: none;
            font-size: 17px;">Insert New Authors</a>
      </div>

      <?php require_once 'authorprocess.php'; ?>

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
      <?php
        $conn = new mysqli($servername, $username, $password) or die(mysqli_error($conn));
        // search
        $result = $conn->query("SELECT * FROM projectdatabase.author") or  die(mysqli_error($conn));
        //pre_r($result);
        //pre_r($result->fetch_assoc());
      ?>
      <!-- <a href = "index.php"
         class = "btn btn-info">Book</a>
      <a href = "authorindex.php"
         class = "btn btn-info">Author</a>
      <a href = "authorindex.php"
         class = "btn btn-info">Recommendation</a> -->

      <form action = "authorprocess.php" method = "POST">
      <button type = "submit" class = "btn btn-success" name = "search" style="float: right; margin-right: .5em; margin-top: .5em;">Search</button>
      <div style="overflow: hidden; padding-right: .5em; ">
        <input type = "text" name = "search_name" class = "form-control" value = "<?php echo $search_name; ?>" placeholder = "Enter author name" style = "margin-top: .5em;">
      </div>
    </form>

      <div class = "row justify-content-center">
        <table class = "table">
          <thead>
             <tr>
              <th>author_id</th>
              <th>name</th>
              <th>rating</th>
              <th>rating_cnt</th>
              <th colspan="2">Action</th>
             </tr>
          </thead>
          <?php
            while($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?php echo $row['author_id'] ?></td>
            <td><?php echo $row['name'] ?></td>
            <td><?php echo $row['rating'] ?></td>
            <td><?php echo $row['rating_cnt'] ?></td>
            <td>
              <a href = "authorindex.php?edit=<?php echo $row['author_id']; ?>"
                 class = "btn btn-info">Edit</a> </td>
            <td>
              <a href = "authorprocess.php?delete=<?php echo $row['author_id']; ?>"
                 class = "btn btn-danger">Delete</a> </td>
          </tr>
          <?php endwhile;?>
        </table>
      </div>

      <?php
        function pre_r($array){
          echo '<pre>';
          print_r($array);
          echo'</pre>';
        }
      ?>


      <div id = "bottom" style = "width:400px; float: left;">
        <p style = "color: Blue; font-size: 25px;"> Insert New Authors Here </p>
      <form action = "authorprocess.php" method = "POST">
        <input type = "hidden" name = "author_id" value = "<?php echo $author_id; ?>">
        <div class = "form-group">
        <label>Name</label>
        <input type = "text" name = "name" class = "form-control" value = "<?php echo $name; ?>" placeholder = "Enter author name">
        </div>
        <div class = "form-group">
        <label>Ratings</label>
        <input type = "INT" name = "rating" class = "form-control" value = "<?php echo $rating; ?>" placeholder = "Enter the author rating">
        </div>
        <div class = "form-group">
        <?php
          if($update == true):?>
            <button type = "submit" class = "btn btn-info" style = "width:400px; margin-bottom: 20px;" name = "update" >Update</button>
        <?php
          else:?>
            <button type = "submit" class = "btn btn-primary" style = "width:400px; margin-bottom: 20px;" name = "save" >Save</button>
        <?php
          endif;?>
        </div>
      </form>
      </div>
      </div>
    </body>

    <div style = "position: absolute; width:400px; float: right; right: 200px;">
      <p style = "color: Blue; font-size: 25px;"> We need your financial support! </p>
        <a href = "authorindex.php">
        <img height = 87px width = 350px src = "Wechat_Pay.png">   </img> </a>
        <a href = "authorindex.php" > <img height = 87px width = 350px src = "Alipay_logo.svg">   </img> </a>
    </div>
