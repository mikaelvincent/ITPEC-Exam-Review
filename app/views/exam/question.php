<?php
// Safeguard against undefined 'question_number'
$questionNumber = isset($question->question_number) ? htmlspecialchars($question->question_number) : 'N/A';
$title = "Q" . $questionNumber . " | ITPEC Exam Review";
?>
<div class="row">
    <div class="col-12 col-lg-8 order-lg-last">
        <div class="row mb-5">
            <div class="col">
                <?php if (!empty($question->image_path)): ?>
                    <img class="img-fluid" src="<?= htmlspecialchars($question->image_path) ?>" alt="Question Image">
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="row gy-3 row-cols-2 row-cols-xl-1 mb-5">
            <?php foreach ($question->getAnswers() as $answer): ?>
                <?php
                $isSelected = isset($user_progress['selected_answer_id']) && $user_progress['selected_answer_id'] === $answer->id;
                $isCorrect = $answer->isCorrect();
                $buttonClass = 'btn-outline-primary';
                
                if (isset($user_progress['is_completed']) && $user_progress['is_completed']) {
                    if ($isSelected && $isCorrect) {
                        $buttonClass = 'btn-success';
                    } elseif ($isSelected && !$isCorrect) {
                        $buttonClass = 'btn-danger';
                    } elseif ($isCorrect) {
                        $buttonClass = 'btn-success';
                    }
                }
                ?>
                <div class="col">
                    <button
                        class="btn <?= $buttonClass ?> btn-lg w-100 py-4"
                        type="button"
                        <?= (isset($user_progress['is_completed']) && $user_progress['is_completed']) ? 'disabled' : '' ?>
                        data-answer-id="<?= htmlspecialchars($answer->id) ?>"
                    >
                        <?= htmlspecialchars($answer->label) ?>
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
        <hr class="mb-5">
        <div class="row gy-3 row-cols-1 mb-5">
            <?php if (isset($user_progress['is_completed']) && !$user_progress['is_completed']): ?>
                <div class="col">
                    <button class="btn btn-success btn-lg w-100" type="button" id="submit-answer">Submit</button>
                </div>
            <?php else: ?>
                <div class="col">
                    <button class="btn btn-success btn-lg disabled w-100" type="button" disabled>Submit</button>
                </div>
            <?php endif; ?>
            <div class="col">
                <div class="modal fade" role="dialog" tabindex="-1" id="explanations">
                    <div class="modal-dialog modal-fullscreen" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title fw-bold">Explanation for Q<?= $questionNumber ?></h4>
                                <button class="btn-close" type="button" aria-label="Close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p>Explanations will be available soon.</p>
                            </div>
                            <div class="modal-footer text-bg-dark d-flex justify-content-center">
                                <div class="me-sm-auto">
                                    <button class="btn btn-link btn-sm" type="button" id="prev-question">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 16 16" class="bi bi-chevron-left fs-3">
                                            <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"></path>
                                        </svg>
                                    </button>
                                    <span class="fs-5"><?= htmlspecialchars($currentQuestionIndex) ?> / <?= htmlspecialchars($totalQuestions) ?></span>
                                    <button class="btn btn-link btn-sm disabled" type="button" disabled>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 16 16" class="bi bi-chevron-right fs-3">
                                            <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"></path>
                                        </svg>
                                    </button>
                                </div>
                                <button class="btn btn-primary btn-lg" type="button" id="generate-explanation">Generate New Explanation</button>
                                <button class="btn btn-secondary btn-lg d-none d-sm-flex" type="button" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
                <button class="btn btn-outline-primary btn-lg w-100" type="button" data-bs-target="#explanations" data-bs-toggle="modal">See Explanation</button>
            </div>
            <div class="col">
                <a href="<?= htmlspecialchars($nextQuestionUrl) ?>" class="btn btn-primary btn-lg w-100" role="button">Next Question</a>
            </div>
            <div class="col">
                <a href="<?= htmlspecialchars($nextQuestionUrl) ?>" class="btn btn-outline-success btn-lg w-100" role="button">Next Question</a>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('submit-answer').addEventListener('click', function() {
        // Implement AJAX submission of the selected answer
        // This is a placeholder for actual implementation
        alert('Submit functionality is not yet implemented.');
    });

    document.getElementById('generate-explanation').addEventListener('click', function() {
        // Implement explanation generation logic
        // This is a placeholder for actual implementation
        alert('Generate Explanation functionality is not yet implemented.');
    });
</script>
