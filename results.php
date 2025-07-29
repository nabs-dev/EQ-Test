<?php
// Start session to access quiz results
session_start();

// Include database connection
require_once 'db.php';

// Redirect to index if quiz not completed
if (!isset($_SESSION['total_score']) || !isset($_SESSION['answers'])) {
    header("Location: index.php");
    exit;
}

$total_score = $_SESSION['total_score'];
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : null;

// Get interpretation based on score
$query = "SELECT * FROM eq_interpretations WHERE ? BETWEEN min_score AND max_score";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $total_score);
$stmt->execute();
$result = $stmt->get_result();
$interpretation = $result->fetch_assoc();

// Calculate percentage score
$max_possible_score = 50; // 10 questions with max score of 5 each
$percentage = round(($total_score / $max_possible_score) * 100);

// Save result to database if form submitted
$result_saved = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_result'])) {
    $user_name = $_POST['user_name'];
    $_SESSION['user_name'] = $user_name;
    
    $save_query = "INSERT INTO eq_results (user_name, total_score) VALUES (?, ?)";
    $save_stmt = $conn->prepare($save_query);
    $save_stmt->bind_param("si", $user_name, $total_score);
    
    if ($save_stmt->execute()) {
        $result_saved = true;
    }
}

// Get breakdown of scores by category
$categories = [
    'Self-Awareness' => [1, 2],
    'Empathy' => [3, 6, 8],
    'Self-Regulation' => [4, 7, 9],
    'Motivation' => [5, 10]
];

$category_scores = [];
foreach ($categories as $category => $question_ids) {
    $category_total = 0;
    $max_possible = count($question_ids) * 5;
    
    foreach ($question_ids as $q_id) {
        if (isset($_SESSION['answers'][$q_id])) {
            $option_id = $_SESSION['answers'][$q_id];
            $score_query = "SELECT score FROM eq_options WHERE id = ?";
            $score_stmt = $conn->prepare($score_query);
            $score_stmt->bind_param("i", $option_id);
            $score_stmt->execute();
            $score_result = $score_stmt->get_result();
            if ($score_row = $score_result->fetch_assoc()) {
                $category_total += $score_row['score'];
            }
        }
    }
    
    $category_percentage = round(($category_total / $max_possible) * 100);
    $category_scores[$category] = [
        'score' => $category_total,
        'max' => $max_possible,
        'percentage' => $category_percentage
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EQ Test Results</title>
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
        
        .results-container {
            max-width: 900px;
            width: 100%;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 40px;
            margin: 20px 0;
            animation: fadeIn 0.8s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .results-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        h1 {
            color: #3a4f9b;
            font-size: 2.2rem;
            margin-bottom: 10px;
        }
        
        .score-overview {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 30px 0;
        }
        
        .score-circle {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: conic-gradient(
                #3a4f9b 0% <?php echo $percentage; ?>%, 
                #e0e0e0 <?php echo $percentage; ?>% 100%
            );
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }
        
        .score-circle::before {
            content: "";
            position: absolute;
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background-color: white;
        }
        
        .score-value {
            position: relative;
            z-index: 1;
            font-size: 2.5rem;
            font-weight: bold;
            color: #3a4f9b;
        }
        
        .score-label {
            font-size: 1rem;
            color: #666;
            margin-top: 5px;
            text-align: center;
        }
        
        .interpretation {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            margin: 30px 0;
        }
        
        .interpretation h2 {
            color: #3a4f9b;
            margin-bottom: 15px;
            font-size: 1.4rem;
        }
        
        .interpretation p {
            color: #444;
            line-height: 1.6;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }
        
        .category-breakdown {
            margin: 40px 0;
        }
        
        .category-breakdown h2 {
            color: #3a4f9b;
            margin-bottom: 20px;
            font-size: 1.4rem;
            text-align: center;
        }
        
        .category {
            margin-bottom: 25px;
        }
        
        .category-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        
        .category-name {
            font-weight: 600;
            color: #333;
        }
        
        .category-score {
            color: #666;
        }
        
        .category-bar {
            width: 100%;
            height: 10px;
            background-color: #e0e0e0;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .category-fill {
            height: 100%;
            border-radius: 5px;
            transition: width 1s ease;
        }
        
        .self-awareness {
            background: linear-gradient(to right, #4CAF50, #8BC34A);
        }
        
        .empathy {
            background: linear-gradient(to right, #3a4f9b, #6a85cc);
        }
        
        .self-regulation {
            background: linear-gradient(to right, #FF9800, #FFC107);
        }
        
        .motivation {
            background: linear-gradient(to right, #9C27B0, #E040FB);
        }
        
        .recommendations {
            background-color: #f0f4ff;
            border-radius: 10px;
            padding: 25px;
            margin: 30px 0;
        }
        
        .recommendations h2 {
            color: #3a4f9b;
            margin-bottom: 15px;
            font-size: 1.4rem;
        }
        
        .recommendations ul {
            list-style-type: none;
            padding-left: 5px;
        }
        
        .recommendations li {
            margin-bottom: 12px;
            padding-left: 25px;
            position: relative;
            color: #444;
            line-height: 1.5;
        }
        
        .recommendations li:before {
            content: "→";
            position: absolute;
            left: 0;
            color: #3a4f9b;
        }
        
        .save-result {
            background-color: white;
            border-radius: 10px;
            padding: 25px;
            margin: 30px 0;
            border: 1px dashed #ccc;
        }
        
        .save-result h2 {
            color: #3a4f9b;
            margin-bottom: 15px;
            font-size: 1.4rem;
            text-align: center;
        }
        
        .save-form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .form-group {
            width: 100%;
            max-width: 400px;
            margin-bottom: 15px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }
        
        .form-input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .form-input:focus {
            border-color: #3a4f9b;
            outline: none;
        }
        
        .save-btn {
            padding: 12px 25px;
            background: linear-gradient(135deg, #3a4f9b 0%, #6a85cc 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .save-btn:hover {
            background: linear-gradient(135deg, #324589 0%, #5a75bc 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(106, 133, 204, 0.4);
        }
        
        .success-message {
            color: #4CAF50;
            margin-top: 15px;
            font-weight: 500;
            text-align: center;
        }
        
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 40px;
        }
        
        .action-btn {
            padding: 15px 25px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
        }
        
        .retake-btn {
            background-color: white;
            color: #3a4f9b;
            border: 2px solid #3a4f9b;
        }
        
        .retake-btn:hover {
            background-color: #f0f4ff;
            transform: translateY(-2px);
        }
        
        .home-btn {
            background: linear-gradient(135deg, #3a4f9b 0%, #6a85cc 100%);
            color: white;
            border: none;
        }
        
        .home-btn:hover {
            background: linear-gradient(135deg, #324589 0%, #5a75bc 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(106, 133, 204, 0.4);
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
            .results-container {
                padding: 25px;
            }
            
            h1 {
                font-size: 1.8rem;
            }
            
            .score-circle {
                width: 150px;
                height: 150px;
            }
            
            .score-circle::before {
                width: 120px;
                height: 120px;
            }
            
            .score-value {
                font-size: 2rem;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 15px;
            }
            
            .action-btn {
                width: 100%;
            }
        }
        
        @media (max-width: 480px) {
            .results-container {
                padding: 20px;
            }
            
            .score-circle {
                width: 120px;
                height: 120px;
            }
            
            .score-circle::before {
                width: 90px;
                height: 90px;
            }
            
            .score-value {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="results-container">
        <div class="results-header">
            <h1>Your EQ Test Results</h1>
            <?php if ($user_name): ?>
                <p>Results for: <?php echo htmlspecialchars($user_name); ?></p>
            <?php endif; ?>
        </div>
        
        <div class="score-overview">
            <div>
                <div class="score-circle">
                    <div class="score-value"><?php echo $percentage; ?>%</div>
                </div>
                <p class="score-label">Your EQ Score: <?php echo $total_score; ?>/<?php echo $max_possible_score; ?></p>
            </div>
        </div>
        
        <div class="interpretation">
            <h2>What Your Score Means</h2>
            <p><?php echo htmlspecialchars($interpretation['interpretation']); ?></p>
        </div>
        
        <div class="category-breakdown">
            <h2>EQ Category Breakdown</h2>
            
            <?php foreach ($category_scores as $category => $data): ?>
                <div class="category">
                    <div class="category-header">
                        <span class="category-name"><?php echo $category; ?></span>
                        <span class="category-score"><?php echo $data['score']; ?>/<?php echo $data['max']; ?></span>
                    </div>
                    <div class="category-bar">
                        <div class="category-fill <?php echo strtolower(str_replace(' ', '-', $category)); ?>" 
                             style="width: <?php echo $data['percentage']; ?>%"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="recommendations">
            <h2>Recommendations for Improvement</h2>
            <p><?php echo htmlspecialchars($interpretation['recommendations']); ?></p>
        </div>
        
        <?php if (!$result_saved): ?>
        <div class="save-result">
            <h2>Save Your Results</h2>
            <form method="post" class="save-form">
                <div class="form-group">
                    <label for="user_name" class="form-label">Your Name</label>
                    <input type="text" id="user_name" name="user_name" class="form-input" required>
                </div>
                <button type="submit" name="save_result" class="save-btn">Save My Results</button>
            </form>
        </div>
        <?php else: ?>
        <div class="save-result">
            <p class="success-message">Your results have been saved successfully!</p>
        </div>
        <?php endif; ?>
        
        <div class="action-buttons">
            <a href="quiz.php?restart=1" class="action-btn retake-btn">Retake Test</a>
            <a href="index.php" class="action-btn home-btn">Back to Home</a>
        </div>
    </div>
    
    <footer>
        &copy; <?php echo date("Y"); ?> EQ Assessment Tool | All Rights Reserved
    </footer>
    
    <script>
        // Animate category bars on page load
        document.addEventListener('DOMContentLoaded', function() {
            const categoryFills = document.querySelectorAll('.category-fill');
            
            setTimeout(() => {
                categoryFills.forEach(fill => {
                    const width = fill.style.width;
                    fill.style.width = '0%';
                    
                    setTimeout(() => {
                        fill.style.width = width;
                    }, 100);
                });
            }, 500);
        });
    </script>
</body>
</html>
