<!DOCTYPE html>
<html>
<head>
    <title>New Leave Application</title>
</head>
<body>

    <h2>New Leave Application</h2>

    <p>An applicant has applied for leave.</p>

    <p><strong>Applicant:</strong> {{ $applicant->name }}</p>

    <p><strong>From:</strong> {{ $leave->from_date }}</p>

    <p><strong>To:</strong> {{ $leave->to_date }}</p>

    <p><strong>Reason:</strong> {{ $leave->reason }}</p>

    <p>Please login to the system to approve or reject this leave.</p>

</body>
</html>