<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Page title
$pageTitle = 'Challenges';

// Fetch challenges
$sql = "SELECT challenges.*, users.fullname AS teacher_name FROM challenges JOIN users ON challenges.teacher_id = users.id ORDER BY challenges.created_at DESC";
$result = mysqli_query($conn, $sql);
$challenges = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Handle challenge answer submission
$message = '';
$error = '';
$showContent = false;
$challengeContent = '';
$answeredChallenge = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_answer']) && isStudent()) {
    $challengeId = (int)$_POST['challenge_id'];
    $answer = trim(sanitize($_POST['answer']));
    
    // Get challenge details
    $stmt = mysqli_prepare($conn, "SELECT file_path, result FROM challenges WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $challengeId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $challenge = mysqli_fetch_assoc($result);
    
    if ($challenge) {
        // Compare the student's answer with the result field
        if (strtolower($answer) === strtolower($challenge['result'])) {
            // Correct answer
            if (file_exists($challenge['file_path'])) {
                // Store in session that this challenge has been answered correctly
                if (!isset($_SESSION['answered_challenges'])) {
                    $_SESSION['answered_challenges'] = [];
                }
                if (!in_array($challengeId, $_SESSION['answered_challenges'])) {
                    $_SESSION['answered_challenges'][] = $challengeId;
                }
                
                $challengeContent = file_get_contents($challenge['file_path']);
                $showContent = true;
                $answeredChallenge = $challengeId;
                $message = "Congratulations! Your answer is correct.";
            } else {
                $error = "Error: Challenge file not found.";
            }
        } else {
            // Wrong answer
            $error = "Incorrect answer. Please try again.";
        }
    } else {
        $error = "Challenge not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <?php if (!empty($message)): ?>
            <?php echo showSuccess($message); ?>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <?php echo showError($error); ?>
        <?php endif; ?>
        
        <div class="row mb-3">
            <div class="col-md-12 d-flex justify-content-between align-items-center">
                <h2>Challenges</h2>
                <?php if (isTeacher()): ?>
                <a href="create-challenge.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Challenge
                </a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Available Challenges</h4>
                    </div>
                    <div class="card-body">
                        <?php if (count($challenges) > 0): ?>
                            <div class="accordion" id="challengesAccordion">
                                <?php foreach ($challenges as $index => $challenge): ?>
                                <div class="card mb-2">
                                    <div class="card-header d-flex justify-content-between align-items-center" id="heading<?php echo $challenge['id']; ?>">
                                        <h5 class="mb-0">
                                            <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse<?php echo $challenge['id']; ?>" aria-expanded="<?php echo ($answeredChallenge == $challenge['id']) ? 'true' : 'false'; ?>" aria-controls="collapse<?php echo $challenge['id']; ?>">
                                                Challenge #<?php echo ($index + 1); ?> - By <?php echo $challenge['teacher_name']; ?>
                                            </button>
                                        </h5>
                                        <span class="badge badge-info">
                                            <i class="fas fa-calendar-alt mr-1"></i>
                                            <?php echo date('d M Y', strtotime($challenge['created_at'])); ?>
                                        </span>
                                    </div>

                                    <div id="collapse<?php echo $challenge['id']; ?>" class="collapse <?php echo ($answeredChallenge == $challenge['id']) ? 'show' : ''; ?>" aria-labelledby="heading<?php echo $challenge['id']; ?>" data-parent="#challengesAccordion">
                                        <div class="card-body">
                                            <div class="challenge-hint mb-4">
                                                <h5>Hint:</h5>
                                                <div class="alert alert-secondary">
                                                    <?php echo nl2br($challenge['hint']); ?>
                                                </div>
                                            </div>
                                            
                                            <?php if (isStudent()): ?>
                                                <?php if ($showContent && $answeredChallenge == $challenge['id']): ?>
                                                    <div class="challenge-content mt-4">
                                                        <h5>Challenge Content:</h5>
                                                        <div class="alert alert-success">
                                                            <div class="mb-2">
                                                                <a href="serve-file.php?type=challenge&id=<?php echo $challenge['id']; ?>" class="btn btn-sm btn-outline-secondary" target="_blank">
                                                                    <i class="fas fa-download"></i> Download File
                                                                </a>
                                                            </div>
                                                            <pre class="mb-0"><?php echo htmlspecialchars($challengeContent); ?></pre>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <form action="" method="post" class="mt-3">
                                                        <input type="hidden" name="challenge_id" value="<?php echo $challenge['id']; ?>">
                                                        <div class="form-group">
                                                            <label for="answer<?php echo $challenge['id']; ?>">Your Answer:</label>
                                                            <input type="text" class="form-control" id="answer<?php echo $challenge['id']; ?>" name="answer" placeholder="Enter your answer here..." required>
                                                            <small class="form-text text-muted">Hint: You need to guess the correct result.</small>
                                                        </div>
                                                        <button type="submit" name="submit_answer" class="btn btn-primary">Submit Answer</button>
                                                    </form>
                                                <?php endif; ?>
                                            <?php elseif (isTeacher()): ?>
                                                <div class="alert alert-info">
                                                    <strong>Note:</strong> This is your challenge. Students will need to guess the correct result to view the content.
                                                </div>
                                                <?php 
                                                    // For teachers, show the answer and file content
                                                    $filePath = $challenge['file_path'];
                                                    $fileContent = file_exists($filePath) ? file_get_contents($filePath) : "File not found";
                                                    
                                                    // Generate secure download link
                                                    $downloadLink = "serve-file.php?type=challenge&id=" . $challenge['id'];
                                                ?>
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6>Answer: <code><?php echo $challenge['result']; ?></code></h6>
                                                        <h6>Content: <a href="<?php echo $downloadLink; ?>" class="btn btn-sm btn-outline-secondary" target="_blank"><i class="fas fa-download"></i> Download</a></h6>
                                                        <pre class="bg-white p-3 border" style="max-height: 300px; overflow-y: auto;"><?php echo htmlspecialchars($fileContent); ?></pre>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> No challenges available yet.
                                <?php if (isTeacher()): ?>
                                    <a href="create-challenge.php" class="alert-link">Create your first challenge</a>.
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
