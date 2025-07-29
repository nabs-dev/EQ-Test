<?php
// Start session to track user progress
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EQ Test - Emotional Intelligence Assessment</title>
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
        
        .container {
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
        
        header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        h1 {
            color: #3a4f9b;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .tagline {
            color: #666;
            font-size: 1.2rem;
            font-style: italic;
        }
        
        .intro-section {
            margin-bottom: 40px;
            line-height: 1.6;
        }
        
        .intro-section p {
            margin-bottom: 15px;
            color: #444;
            font-size: 1.1rem;
        }
        
        .eq-benefits {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            margin: 30px 0;
        }
        
        .eq-benefits h3 {
            color: #3a4f9b;
            margin-bottom: 15px;
            font-size: 1.3rem;
        }
        
        .benefits-list {
            list-style-type: none;
        }
        
        .benefits-list li {
            margin-bottom: 12px;
            padding-left: 30px;
            position: relative;
            color: #555;
        }
        
        .benefits-list li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #4CAF50;
            font-weight: bold;
        }
        
        .start-button {
            display: block;
            width: 200px;
            margin: 40px auto 20px;
            padding: 15px 0;
            background: linear-gradient(135deg, #3a4f9b 0%, #6a85cc 100%);
            color: white;
            text-align: center;
            border-radius: 50px;
            font-size: 1.2rem;
            font-weight: 600;
            text-decoration: none;
            box-shadow: 0 4px 15px rgba(106, 133, 204, 0.4);
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
        }
        
        .start-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(106, 133, 204, 0.6);
        }
        
        .start-button:active {
            transform: translateY(1px);
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
            .container {
                padding: 25px;
            }
            
            h1 {
                font-size: 2rem;
            }
            
            .tagline {
                font-size: 1rem;
            }
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 20px;
            }
            
            h1 {
                font-size: 1.8rem;
            }
            
            .start-button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Emotional Intelligence Assessment</h1>
            <p class="tagline">Discover your EQ and unlock your emotional potential</p>
        </header>
        
        <section class="intro-section">
            <p>Welcome to our comprehensive Emotional Intelligence (EQ) test. This assessment will help you understand your ability to recognize, understand, and manage emotions in yourself and others.</p>
            
            <p>Emotional intelligence is a critical factor in personal and professional success. Unlike IQ, emotional intelligence can be developed and improved throughout your life.</p>
            
            <div class="eq-benefits">
                <h3>Benefits of High Emotional Intelligence:</h3>
                <ul class="benefits-list">
                    <li>Better communication and relationships</li>
                    <li>Improved decision-making abilities</li>
                    <li>Enhanced leadership skills</li>
                    <li>Greater resilience to stress</li>
                    <li>Higher job performance and satisfaction</li>
                </ul>
            </div>
            
            <p>This test consists of 10 questions and takes approximately 5 minutes to complete. For each question, select the option that best describes your typical behavior or feelings.</p>
            
            <p>After completing the test, you'll receive your EQ score along with personalized feedback and recommendations for improvement.</p>
        </section>
        
        <button class="start-button" id="startTest">Start EQ Test</button>
    </div>
    
    <footer>
        &copy; <?php echo date("Y"); ?> EQ Assessment Tool | All Rights Reserved
    </footer>
    
    <script>
        // JavaScript for redirection as requested
        document.getElementById('startTest').addEventListener('click', function() {
            window.location.href = 'quiz.php';
        });
    </script>
</body>
</html>
