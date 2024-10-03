<div class="container">
    <div class="row">
        <div class="col-8">
            <h1 class="heading">Questions</h1>
            <?php
            include("./common/db.php");

            // Initialize variables if they are not set
            $uid = isset($_GET['u-id']) ? $_GET['u-id'] : null;
            $cid = isset($_GET['c-id']) ? $_GET['c-id'] : null;
            $search = isset($_GET['search']) ? $_GET['search'] : null;

            if (isset($_GET["c-id"])) {
                $query = "SELECT * FROM questions WHERE category_id=$cid";
            } elseif (isset($_GET["u-id"])) {
                $query = "SELECT * FROM questions WHERE user_id=$uid";
            } elseif (isset($_GET["latest"])) {
                $query = "SELECT * FROM questions ORDER BY id DESC";
            } elseif (isset($_GET["search"])) {
                $query = "SELECT * FROM questions WHERE `title` LIKE '%$search%'";
            } else {
                $query = "SELECT * FROM questions";
            }

            $result = $conn->query($query);
            foreach ($result as $row) {
                $title = $row['title'];
                $id = $row['id'];
                echo "<div class='row question-list'>
                    <h4 class='my-question'><a href='?q-id=$id'>$title</a>";
                echo $uid ? "<a href='./server/requests.php?delete=$id'>Delete</a>" : NULL;
                echo "</h4></div>";
            }
            ?>
        </div>
        <div class="col-4">
            <?php include('categorylist.php'); ?>
        </div>
    </div>
</div>
