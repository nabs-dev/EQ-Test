<?php
// Start session to track user progress
session_start();

// Include database connection
require_once 'db.php';

// Initialize or reset the quiz state
if (!isset($_SESSION['current_question']) || isset($_GET['restart'])) {
    $_SESSION['current_question'] = 1;
    $_SESSION['total_score'] = 0;
    $_SESSION['answers'] = [];
}

// Get current question
$current = $_SESSION['current_question'];
$total_questions = 10; // We have 10 questions in our database

// Process answer submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answer'])) {
    $question_id = $_POST['question_id'];
    $selected_option = $_POST['answer'];
    
    // Store the answer and score
    $query = "SELECT score FROM eq_options WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $selected_option);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $_SESSION['total_score'] += $row['score'];
        $_SESSION['answers'][$question_id] = $selected_option;
    }
    
    // Move to next question or results page
    $_SESSION['current_question']++;
    
    if ($_SESSION['current_question'] > $total_questions) {
        // Quiz completed, redirect to results
        header("Location: results.php");
        exit;
    }
    
    // Refresh the page to show next question
    header("Location: quiz.php");
    exit;
}

// Get the current question data
$query = "SELECT * FROM eq_questions WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $current);
$stmt->execute();
$question_result = $stmt->get_result();
$question = $question_result->fetch_assoc();

// Get options for the current question
$options_query = "SELECT * FROM eq_options WHERE question_id = ? ORDER BY id";
$options_stmt = $conn->prepare($options_query);
$options_stmt->bind_param("i", $current);
$options_stmt->execute();
$options_result = $options_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EQ Test - Question <?php echo $current; ?></title>
    <style>
        /* Internal CSS as requested */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }
        
        .quiz-container {
            max-width: 800px;
            width: 100%;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 40px;
            margin: 20px 0;
            animation: fadeIn 0.5s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .quiz-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        h1 {
            color: #3a4f9b;
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .progress-container {
            margin: 20px 0 30px;
        }
        
        .progress-text {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            color: #666;
        }
        
        .progress-bar {
            width: 100%;
            height: 10px;
            background-color: #e0e0e0;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(to right, #3a4f9b, #6a85cc);
            border-radius: 5px;
            transition: width 0.3s ease;
        }
        
        .question {
            background-color: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .question-text {
            font-size: 1.3rem;
            color: #333;
            line-height: 1.5;
            margin-bottom: 5px;
        }
        
        .question-number {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 15px;
        }
        
        .options-container {
            margin-top: 25px;
        }
        
        .option-label {
            display: block;
            padding: 15px 20px;
            margin-bottom: 12px;
            background-color: #fff;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
        }
        
        .option-label:hover {
            border-color: #6a85cc;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }
        
        .option-input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }
        
        .option-input:checked + .option-label {
            border-color: #3a4f9b;
            background-color: #f0f4ff;
        }
        
        .option-text {
            padding-left: 30px;
            position: relative;
            color: #444;
            font-size: 1.1rem;
        }
        
        .option-text:before {
            content: "";
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            border: 2px solid #ccc;
            border-radius: 50%;
        }
        
        .option-input:checked + .option-label .option-text:before {
            border-color: #3a4f9b;
            background-color: #3a4f9b;
        }
        
        .option-input:checked + .option-label .option-text:after {
            content: "";
            position: absolute;
            left: 7px;
            top: 50%;
            transform: translateY(-50%);
            width: 6px;
            height: 6px;
            background-color: white;
            border-radius: 50%;
        }
        
        .submit-btn {
            display: block;
            width: 100%;
            padding: 15px 0;
            margin-top: 30px;
            background: linear-gradient(135deg, #3a4f9b 0%, #6a85cc 100%);
            color: white;
            text-align: center;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .submit-btn:hover {
            background: linear-gradient(135deg, #324589 0%, #5a75bc 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(106, 133, 204, 0.4);
        }
        
        .submit-btn:disabled {
            background: #cccccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        footer {
            text-align: center;
            margin-top: auto;
            padding: 20px 0;
            color: #666;
            font-size: 0.9rem;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .quiz-container {
                padding: 25px;
            }
            
            h1 {
                font-size: 1.8rem;
            }
            
            .question-text {
                font-size: 1.2rem;
            }
        }
        
        @media (max-width: 480px) {
            .quiz-container {
                padding: 20px;
            }
            
            .option-label {
                padding: 12px 15px;
            }
            
            .option-text {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="quiz-container">
        <div class="quiz-header">
            <h1>Emotional Intelligence Assessment</h1>
        </div>
        
        <div class="progress-container">
            <div class="progress-text">
                <span>Question <?php echo $current; ?> of <?php echo $total_questions; ?></span>
                <span><?php echo round(($current - 1) / $total_questions * 100); ?>% Complete</span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?php echo (($current - 1) / $total_questions * 100); ?>%"></div>
            </div>
        </div>
        
        <div class="question">
            <p class="question-number">Question <?php echo $current; ?></p>
            <p class="question-text"><?php echo htmlspecialchars($question['question_text']); ?></p>
            
            <form method="post" id="quizForm">
                <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
                
                <div class="options-container">
                    <?php while ($option = $options_result->fetch_assoc()): ?>
                        <div class="option">
                            <input type="radio" name="answer" id="option<?php echo $option['id']; ?>" 
                                   value="<?php echo $option['id']; ?>" class="option-input" required>
                            <label for="option<?php echo $option['id']; ?>" class="option-label">
                                <span class="option-text"><?php echo htmlspecialchars($option['option_text']); ?></span>
                            </label>
                        </div>
                    <?php endwhile; ?>
                </div>
                
                <button type="submit" class="submit-btn" id="submitBtn" disabled>Continue</button>
            </form>
        </div>
    </div>
    
    <footer>
        &copy; <?php echo date("Y"); ?> EQ Assessment Tool | All Rights Reserved
    </footer>
    
    <script>
        // Enable submit button when an option is selected
        const radioButtons = document.querySelectorAll('input[type="radio"]');
        const submitButton = document.getElementById('submitBtn');
        
        radioButtons.forEach(button => {
            button.addEventListener('change', function() {
                submitButton.disabled = false;
            });
        });
    </script>
</body>
</html>
