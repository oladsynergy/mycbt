<?php
// Home page content
?>
<h1>Welcome to the CBT Exam App</h1>
<p>This is a Computer-Based Testing (CBT) application for conducting online exams.</p>

<?php if (!is_logged_in()): ?>
    <p>Please <a href="index.php?page=login">login</a> or <a href="index.php?page=register">register</a> to access the exams.</p>
<?php else: ?>
    <p>Welcome back, <?php echo $_SESSION['username']; ?>! Go to your <a href="index.php?page=dashboard">dashboard</a> to view available exams or manage your account.</p>
<?php endif; ?>

<h2>Features</h2>
<ul>
    <li>Multiple choice questions</li>
    <li>Timed exams</li>
    <li>Instant results</li>
    <li>Teacher and student roles</li>
</ul>