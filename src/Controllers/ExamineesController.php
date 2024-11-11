<?php 

namespace App\Controllers;

use App\Models\UserAnswer;
use App\Models\User;
use Fpdf\Fpdf;




class ExamineesController extends BaseController
{
    public function index() {
        $this->initializeSession();
        if (isset($_SESSION['is_logged_in'])) {
            $userObj = new User();
            $examinees = $userObj->getAllUsers();
    
            $userAnsObj = new UserAnswer();
            $data = $userAnsObj->getUserAnswers();
    
            $combinedData = [
                'examinees' => $examinees,
                'data' => $data, // Assuming this is also an array
            ];
    
            return $this->render('examinees', $combinedData);
        }
        header("Location: /login");
    }

    public function exportToPDF($attempt_id)
    {
        // Initialize UserAnswer object and fetch data
        $obj = new UserAnswer();
        $data = $obj->exportData($attempt_id);
    
        // Create an instance of FPDF
        $pdf = new FPDF();
        $pdf->AddPage();
    
        // Set document title with larger, bold font
        $pdf->SetFont('Arial', 'B', 20);
        $pdf->SetTextColor(0, 51, 102); // Dark blue color for the title
        $pdf->Cell(0, 15, 'Examinee Attempt Details', 0, 1, 'C');
        $pdf->Ln(10); // Add space after title
    
        // Section: Examinee Information
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetFillColor(0, 51, 102); // Dark blue background for headers
        $pdf->SetTextColor(255, 255, 255); // White color for header text
        $pdf->Cell(0, 10, 'Examinee Information', 0, 1, 'C', true);
        $pdf->Ln(5);
    
        $pdf->SetFont('Arial', '', 12);
        $pdf->SetTextColor(0, 0, 0); // Black for body text
    
        // Use a loop for dynamic content
        $info = [
            'Name' => $data['examinee_name'],
            'Email' => $data['examinee_email'],
            'Attempt Date' => $data['attempt_date'],
            'Exam Items' => $data['exam_items'],
            'Exam Score' => $data['exam_score']
        ];
    
        foreach ($info as $label => $value) {
            $pdf->Cell(50, 10, $label . ':', 1);
            $pdf->Cell(0, 10, $value, 1, 1);
        }
        $pdf->Ln(10); // Add space before next section
    
        // Section: Answers Submitted
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetFillColor(0, 51, 102);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(0, 10, 'Answers Submitted', 0, 1, 'C', true);
        $pdf->Ln(5);
    
        $pdf->SetFont('Arial', '', 12);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(0, 10, 'Answers: ' . $data['answers'], 1);
        $pdf->Cell(50, 10, 'Date Answered:', 1);
        $pdf->Cell(0, 10, $data['date_answered'], 1, 1);
        $pdf->Ln(10); // Add space before footer
    
        // Section: Questions and Correct Answers
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetFillColor(0, 51, 102);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(0, 10, 'Questions and Correct Answers', 0, 1, 'C', true);
        $pdf->Ln(5);
    
        // Footer with page number
        $pdf->SetY(-15); // Position at 1.5 cm from bottom
        $pdf->SetFont('Arial', 'I', 10);
        $pdf->SetTextColor(128); // Gray color for footer text
        $pdf->Cell(0, 10, 'Page ' . $pdf->PageNo(), 0, 0, 'C');
    
        // Output the PDF as a download
        $pdf->Output('D', 'examinee_attempt_' . $attempt_id . '.pdf');
    }
    

}