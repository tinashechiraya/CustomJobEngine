<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Available Jobs</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <h1 class="mb-4 text-center">Available Jobs</h1>
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Class</th>
                    <th>Methods</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="jobTable">
                @foreach ($jobClasses as $index => $job)
                    <tr class="job-row">
                        <td>{{ $job['class'] }}</td>
                        <td>
                        <ul class="list-unstyled mb-0">
                            @foreach ($job['methods'] as $method)
                                <li>{{ $method['name'] }}</li>
                            @endforeach
                        </ul>

                        </td>
                        <td>
                        <button type="button" class="btn btn-primary open-dispatch-modal" 
                            data-job="{{ json_encode($job) }}" 
                            data-action="{{ route('jobs.dispatch') }}">Dispatch</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <!-- Pagination -->
        <nav>
            <ul class="pagination justify-content-center" id="pagination"></ul>
        </nav>

        <h2 class="mt-5 mb-4 text-center">Queued Jobs</h2>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-primary">
                    <tr>
                        <th>ID</th>
                        <th>Class</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Retry Count</th>
                        <th>Error Log</th>
                        <th>Actions</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody id="jobStatusTable">
                    @forelse ($queuedJobs as $queued)
                        <tr id="job-{{ $queued->id }}">
                            <td>{{ $queued->id }}</td>
                            <td>{{ $queued->job_class }}</td>
                            <td>{{ $queued->job_method }}</td>
                            <td id="status-{{ $queued->id }}">
                                <span class="badge bg-warning text-dark">{{ ucfirst($queued->status) }}</span>
                            </td>
                            <td>{{ $queued->retry_count }}</td>
                            <td>
                                @if ($queued->error_log)
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#errorModal-{{ $queued->id }}">
                                        View
                                    </button>

                                    <!-- Error Modal -->
                                    <div class="modal fade" id="errorModal-{{ $queued->id }}" tabindex="-1" aria-labelledby="errorModalLabel-{{ $queued->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="errorModalLabel-{{ $queued->id }}">Error Log</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <pre>{{ $queued->error_log }}</pre>
                                        </div>
                                        </div>
                                    </div>
                                    </div>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if ($queued->status === 'running')
                                    <form method="POST" action="{{ route('jobs.cancel', $queued->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-danger">Cancel</button>
                                    </form>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $queued->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No jobs queued yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="d-flex justify-content-center mt-3">
                {{ $queuedJobs->links() }}
            </div>

        </div>

    </div>

    <!-- Modal for Dispatch Form -->
    <div class="modal fade" id="dispatchModal" tabindex="-1" aria-labelledby="dispatchModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="modalDispatchForm" method="POST" action="">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="dispatchModalLabel">Dispatch Job</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          
          <!-- Hidden Class Field -->
          <input type="hidden" name="class" id="modalClassInput">
          
          <!-- Method Dropdown -->
          <div class="mb-3">
            <label for="modalMethodSelect">Method</label>
            <select class="form-select" name="method" id="modalMethodSelect">
              <!-- Dynamically filled with methods -->
            </select>
          </div>

          <!-- Delay and Priority Inputs -->
          <div class="mb-3">
            <label>Delay (seconds)</label>
            <input type="number" name="delay" class="form-control" min="0">
          </div>

          <div class="mb-3">
            <label>Priority</label>
            <input type="number" name="priority" class="form-control">
          </div>

          <!-- Dynamic Parameter Fields -->
          <div id="modalParamsContainer" class="d-flex flex-column gap-2"></div>

        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Confirm Dispatch</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>



</div>

<!-- Bootstrap 5 JS Bundle (for dropdowns etc.) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Simple Pagination Script -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const rowsPerPage = 5;
        const rows = document.querySelectorAll('.job-row');
        const pagination = document.getElementById('pagination');
        let currentPage = 1;
        const totalPages = Math.ceil(rows.length / rowsPerPage);

        function showPage(page) {
            rows.forEach((row, index) => {
                row.style.display = (index >= (page-1)*rowsPerPage && index < page*rowsPerPage) ? '' : 'none';
            });
        }

        function setupPagination() {
            pagination.innerHTML = '';
            for (let i = 1; i <= totalPages; i++) {
                const li = document.createElement('li');
                li.className = 'page-item ' + (i === currentPage ? 'active' : '');
                li.innerHTML = `<a href="#" class="page-link">${i}</a>`;
                li.addEventListener('click', function (e) {
                    e.preventDefault();
                    currentPage = i;
                    showPage(currentPage);
                    setupPagination();
                });
                pagination.appendChild(li);
            }
        }

        showPage(currentPage);
        setupPagination();
    });
</script>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const dispatchButtons = document.querySelectorAll('.open-dispatch-modal');
    const modal = new bootstrap.Modal(document.getElementById('dispatchModal'));
    const modalForm = document.getElementById('modalDispatchForm');
    const modalClassInput = document.getElementById('modalClassInput');
    const modalMethodSelect = document.getElementById('modalMethodSelect');
    const modalParamsContainer = document.getElementById('modalParamsContainer');

    dispatchButtons.forEach(button => {
        button.addEventListener('click', function () {
            // Get job data (including methods) from the button's data attribute
            const job = JSON.parse(button.getAttribute('data-job'));
            
            // Get form action from button's data-action attribute
            const formAction = button.getAttribute('data-action');
            if (!formAction) {
                console.error('Form action not found!');
                return;
            }
            modalForm.action = formAction; // Set form action

            // Fill hidden class input in modal
            modalClassInput.value = job.class;

            // Clear existing method options in the dropdown
            modalMethodSelect.innerHTML = '';

            // Populate method select dropdown
            if (Array.isArray(job.methods)) {
                Object.values(job.methods).forEach(method => {
                    const option = document.createElement('option');
                    option.value = method.name;
                    option.textContent = method.name;
                    modalMethodSelect.appendChild(option);
                });

                const selectedMethod = job.methods[0];
                modalMethodSelect.value = selectedMethod.name;

                modalParamsContainer.innerHTML = '';

                selectedMethod.parameters.forEach(param => {
                    const input = document.createElement('input');
                    input.type = 'text';
                    input.name = 'params[]';
                    input.classList.add('form-control');
                    input.placeholder = param;
                    modalParamsContainer.appendChild(input);
                });
            } else {
                console.error('job.methods is not an array:', job.methods);
            }


            // Show the modal
            modal.show();

            // Update parameters when method is changed in the modal
            modalMethodSelect.addEventListener('change', function () {
                const selectedMethodName = this.value;
                const selectedMethod = job.methods.find(m => m.name === selectedMethodName);

                // Clear previous parameters
                modalParamsContainer.innerHTML = '';

                // Add new parameters
                selectedMethod.parameters.forEach(param => {
                    const input = document.createElement('input');
                    input.type = 'text';
                    input.name = 'params[]';
                    input.classList.add('form-control');
                    input.placeholder = param;
                    modalParamsContainer.appendChild(input);
                });
            });
        });
    });
});

</script>



</body>
</html>
