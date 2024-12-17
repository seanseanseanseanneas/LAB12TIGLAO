<?php 

namespace App\Controllers;

use App\Models\Question;
use App\Models\UserAnswer;
use App\Models\User;
use TCPDF;

require_once __DIR__ . '/../../vendor/autoload.php';
class ExamController extends BaseController
{
    public function registrationForm()
    {
        $this->initializeSession();

        return $this->render('registration-form');
    }

    public function register()
    {
        $this->initializeSession();
        $data = $_POST;
        
        $userObj = new User();
        $user_id = $userObj->save($data);

        $_SESSION['user_id'] = $user_id;
        $_SESSION['complete_name'] = $data['complete_name'];
        $_SESSION['email'] = $data['email'];

        return $this->render('login-form', $data);
    }

    public function loginForm()
    {
        $this->initializeSession();

        return $this->render('login-form');
    }

    public function login()
    {
        $this->initializeSession();
        $data = $_POST;

        // Retrieve user by email
        $userObj = new User();
        $user = $userObj->findByEmail($data['email']);

        if ($user && password_verify($data['password'], $user['password_hash'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['complete_name'] = $user['complete_name'];
            $_SESSION['email'] = $user['email'];
            return $this->render('pre-exam');

        } else {
            $error = "Invalid email or password.";
            return $this->render('login-form', ['error' => $error]);
        }
    }

    public function exam()
    {
        $this->initializeSession();
        $item_number = 1;

        // If request is coming from the form, save the inputs to the session
        if (isset($_POST['item_number']) && isset($_POST['answer'])) {
            array_push($_SESSION['answers'], $_POST['answer']);
            $_SESSION['item_number'] = $_POST['item_number'] + 1;
        }

        if (!isset($_SESSION['item_number'])) {
            // Initialize session variables
            $_SESSION['item_number'] = $item_number;
            $_SESSION['answers'] = [false];
        } else {
            $item_number = $_SESSION['item_number'];
        }

        $data = $_POST;
        $questionObj = new Question();
        $question = $questionObj->getQuestion($item_number);

        // if there are no more questions, save the answers
        if (is_null($question) || !$question) {
            $user_id = $_SESSION['user_id'];
            $json_answers = json_encode($_SESSION['answers']);

            error_log('FINISHED EXAM, SAVING ANSWERS');
            error_log('USER ID = ' . $user_id);
            error_log('ANSWERS = ' . $json_answers);

            $userAnswerObj = new UserAnswer();
            $userAnswerObj->save(
                $user_id,
                $json_answers
            );
            $score = $questionObj->computeScore($_SESSION['answers']);
            $items = $questionObj->getTotalQuestions();
            $userAnswerObj->saveAttempt($user_id, $items, $score);

            header("Location: /result");
            exit;
        }

        $question['choices'] = json_decode($question['choices']);

        return $this->render('exam', $question);
    }

    public function result()
    {
        $this->initializeSession();
        $data = $_SESSION;
        $questionObj = new Question();
        $data['questions'] = $questionObj->getAllQuestions();
        $answers = $_SESSION['answers'];
        foreach ($data['questions'] as &$question) {
            $question['choices'] = json_decode($question['choices']);
            $question['user_answer'] = $answers[$question['item_number']];
        }
        $data['total_score'] = $questionObj->computeScore($_SESSION['answers']);
        $data['question_items'] = $questionObj->getTotalQuestions();

        session_destroy();

        return $this->render('result', $data);
    }

    public function examinees()
    {
        $this->initializeSession();
        $userAnswerObj = new UserAnswer();
        $examAttempts = $userAnswerObj->getAllExamAttempts();

        foreach ($examAttempts as &$attempt) {
            $attempt['attempt_date'] = date('Y-m-d H:i:s', strtotime($attempt['attempt_date']));
    }

        return $this->render('examinees', ['examAttempts' => $examAttempts]);
    }

    public function examAttempt($id)
    {
        $userAnswerObj = new UserAnswer();
    $examAttemptDetails = $userAnswerObj->getExamAttemptById($id);

    if (!$examAttemptDetails) {
        return $this->render('error', ['message' => 'Exam attempt not found']);
    }

    // Create new PDF document
    $pdf = new TCPDF();
    $pdf->AddPage();

    // Set PDF properties
    $pdf->SetTitle('Exam Attempt Report');
    $pdf->SetFont('helvetica', '', 12);

    // Add a title
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 121, 107); // Title color (#00796b)
    $pdf->Cell(0, 10, 'Exam Attempt Report', 0, 1, 'C');
    $pdf->Ln(10); // Add some space after the title

    // Create table for details
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetFillColor(0, 121, 107); // Header background color (#00796b)
    $pdf->SetTextColor(255, 255, 255); // Header text color (white)
    $pdf->Cell(90, 10, 'Detail', 1, 0, 'C', true);
    $pdf->Cell(100, 10, 'Value', 1, 1, 'C', true);

    // Reset colors for the table content
    $pdf->SetFont('helvetica', '', 12);
    $pdf->SetTextColor(0, 0, 0); // Text color for content

    $pdf->Cell(90, 10, 'Examinee Name:', 1);
    $pdf->Cell(100, 10, $examAttemptDetails['complete_name'], 1, 1);
    $pdf->Cell(90, 10, 'Email:', 1);
    $pdf->Cell(100, 10, $examAttemptDetails['email'], 1, 1);
    $pdf->Cell(90, 10, 'Total Score:', 1);
    $pdf->Cell(100, 10, $examAttemptDetails['total_score'], 1, 1);
    $pdf->Cell(90, 10, 'Exam Attempt Date:', 1);
    $pdf->Cell(100, 10, $examAttemptDetails['attempt_date'], 1, 1);

    // Add a footer
    $pdf->Ln(10); // Add space before footer
    $pdf->SetFont('helvetica', 'I', 10);
    $pdf->SetTextColor(100, 100, 100); // Footer text color
    $pdf->Cell(0, 10, 'Thank you for participating in the exam!', 0, 1, 'C');

    // Output PDF
    $pdf->Output('exam_attempt_' . $examAttemptDetails['id'] . '.pdf', 'D'); // Force download
    
    exit;
    }
}
