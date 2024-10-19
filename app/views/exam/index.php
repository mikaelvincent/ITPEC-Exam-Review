<?php
$title = htmlspecialchars($exam_name) . " | ITPEC Exam Review"; ?>

<div class="row gy-3 row-cols-1 mb-5">
    <div class="col">
        <a class="btn btn-primary btn-lg w-100" role="button" href="#">IP Exam</a>
    </div>
    <div class="col">
        <a class="btn btn-outline-success btn-lg w-100" role="button" href="#">FE Exam</a>
    </div>
    <div class="col">
        <a class="btn btn-primary btn-lg disabled w-100" role="button" href="#">AP Exam</a>
    </div>
</div>
<div class="row gy-3 row-cols-1 mb-3 collapse show" id="expand-button-row">
    <div class="col d-flex justify-content-center">
        <button class="btn btn-link btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-menu, #expand-button-row">
            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 16 16" class="bi bi-chevron-down fs-2">
                <path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z"></path>
            </svg>
        </button>
    </div>
</div>
<div id="collapse-menu" class="collapse">
    <div class="row gy-3 row-cols-1 mb-5">
        <div class="col">
            <a class="btn btn-outline-info btn-lg w-100" role="button" href="#">Contributors</a>
        </div>
        <div class="col">
            <button class="btn btn-outline-info btn-lg w-100" type="button" data-bs-target="#website-stats" data-bs-toggle="modal">Website Stats</button>
            <div class="modal fade" role="dialog" tabindex="-1" id="website-stats">
                <div class="modal-dialog modal-xl" role="document">
                    <div class="modal-content">
                        <div class="modal-body bg-info-subtle">
                            <div class="d-flex justify-content-end">
                                <button class="btn-close" type="button" aria-label="Close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="text-center text-info-emphasis">
                                <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4">
                                    <div class="col">
                                        <div class="p-3">
                                            <h4 class="display-5 fw-bold mb-0">123</h4>
                                            <p class="mb-0">Unique Visitors</p>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="p-3">
                                            <h4 class="display-5 fw-bold mb-0">123</h4>
                                            <p class="mb-0">Total Pages Viewed</p>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="p-3">
                                            <h4 class="display-5 fw-bold mb-0">123</h4>
                                            <p class="mb-0">Available Questions</p>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="p-3">
                                            <h4 class="display-5 fw-bold mb-0">123</h4>
                                            <p class="mb-0">Explanations Generated</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row gy-3 row-cols-1 mb-5">
        <div class="col">
            <div class="modal fade" role="dialog" tabindex="-1" id="reset-progress-confirm">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title fw-bold">Confirm Progress Reset</h4>
                            <button class="btn-close" type="button" aria-label="Close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to reset all your progress?</p>
                            <p>This action is irreversible and cannot be undone.</p>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-danger" type="button">Reset All Progress</button>
                        </div>
                    </div>
                </div>
            </div>
            <button class="btn btn-outline-warning btn-lg w-100" type="button" data-bs-target="#reset-progress-confirm" data-bs-toggle="modal">Reset All Progress</button>
        </div>
    </div>
</div>
