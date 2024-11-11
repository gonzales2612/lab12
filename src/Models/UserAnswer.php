<?php

namespace App\Models;

use App\Models\BaseModel;
use \PDO;

class UserAnswer extends BaseModel
{
    protected $user_id; // ID of the user
    protected $answers; // User's answers to exam questions

    // Method to save user's answers
    public function save($user_id, $answers, $attempt_id)
    {
        $this->user_id = $user_id; // Set the user ID
        $this->answers = $answers; // Set the user's answers

        // SQL query to insert user's answers into the database
        $sql = "INSERT INTO users_answers
                SET
                    user_id=:user_id,
                    answers=:answers,
                    attempt_id=:attempt_id";        
        $statement = $this->db->prepare($sql); // Prepare the SQL statement
        $statement->execute([ // Execute the statement with provided parameters
            'user_id' => $user_id,
            'answers' => $answers,
            'attempt_id' => $attempt_id
        ]);
    
        return $statement->rowCount(); // Return the number of affected rows
    }

    // Method to save a user's exam attempt
    public function saveAttempt($user_id, $exam_items, $score)
    {
        // SQL query to insert an exam attempt into the database
        $sql = "INSERT INTO exam_attempts
                SET
                    user_id=:user_id,
                    exam_items=:exam_items,
                    exam_score=:exam_score";   
        $statement = $this->db->prepare($sql); // Prepare the SQL statement
        $statement->execute([ // Execute the statement with provided parameters
            'user_id' => $user_id,
            'exam_items' => $exam_items,
            'exam_score' => $score
        ]);
        
        return $this->db->lastInsertId(); // Return the last inserted ID (attempt ID)
    }

    // Method to retrieve all user answers with related data
    public function getUserAnswers() {
        // SQL query to select user answers and related information
        $sql = "
        SELECT 
            ua.answer_id,
            ua.attempt_id,
            ua.answers,
            ua.date_answered,
            ea.attempt_datetime AS attempt_date,
            u.complete_name AS examinee_name,
            ea.exam_items,
            ea.exam_score
        FROM 
            users_answers AS ua
        JOIN 
            users AS u ON ua.user_id = u.id
        JOIN 
            exam_attempts AS ea ON ua.attempt_id = ea.id  -- Change `ea.attempt_id` to `ea.id` if the column is named `id`
        ORDER BY 
            ua.date_answered DESC"; // Order results by the date answered, most recent first
    
        // Prepare the SQL statement
        $stmt = $this->db->prepare($sql);
        
        // Execute the statement
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch all results as an associative array
        return $result; // Return the array of user answers
    }

    // Method to export data for a specific exam attempt
    public function exportData($attempt_id) {
        // SQL query to select user answers and related information for a specific attempt
        $sql = "
            SELECT 
                ua.answer_id,
                ua.attempt_id,
                ua.answers,
                ua.date_answered,
                ea.attempt_datetime AS attempt_date,
                u.complete_name AS examinee_name,
                u.email AS examinee_email,  -- Added examinee email field for export
                ea.exam_items,
                ea.exam_score
            FROM 
                users_answers AS ua
            JOIN 
                users AS u ON ua.user_id = u.id
            JOIN 
                exam_attempts AS ea ON ua.attempt_id = ea.id  -- Corrected this line, using ea.id instead of ea.attempt_id
            WHERE 
                ea.id = :attempt_id  -- Use ea.id here as well
            ORDER BY 
                ua.date_answered DESC"; // Order results by date answered
        
        $stmt = $this->db->prepare($sql); // Prepare the SQL statement
        
        $stmt->bindParam(':attempt_id', $attempt_id, PDO::PARAM_INT); // Bind the attempt ID parameter
        
        $stmt->execute(); // Execute the statement
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch the result as an associative array
        return $result; // Return the exported data for the specific attempt
    }
}
