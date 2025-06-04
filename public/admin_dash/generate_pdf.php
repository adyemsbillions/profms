<?php
// Include the TCPDF library
require_once('tcpdf/tcpdf.php');

// Create a new PDF document
$pdf = new TCPDF();

// Set document properties
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your Company');
$pdf->SetTitle('Database Export');
$pdf->SetSubject('Exported Data as PDF');
$pdf->SetKeywords('TCPDF, PDF, export, database');

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('helvetica', '', 10);

// Add Title
$pdf->Cell(0, 10, 'Database Export - Articles, Journals, Payments, Users, Inquiries, Admin', 0, 1, 'C');

// Articles Table
$pdf->Ln(10);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Articles Data', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);
$html = '
<table border="1" cellpadding="5">
    <thead>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Status</th>
            <th>Date Submitted</th>
        </tr>
    </thead>
    <tbody>
        <tr><td>1</td><td>Aspernatur illum et</td><td>submitted</td><td>2025-06-03 15:08:41</td></tr>
        <tr><td>2</td><td>Aliquam rem sunt iru</td><td>rejected</td><td>2025-06-03 15:14:36</td></tr>
        <tr><td>3</td><td>Aspernatur illum et</td><td>approved</td><td>2025-06-03 15:22:07</td></tr>
        <tr><td>4</td><td>Maxime ut impedit i</td><td>submitted</td><td>2025-06-04 12:54:06</td></tr>
        <tr><td>5</td><td>Nemo quibusdam ut in</td><td>draft</td><td>2025-06-04 12:59:41</td></tr>
    </tbody>
</table>
';
$pdf->writeHTML($html, true, false, true, false, '');

// Journals Table
$pdf->Ln(10);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Journals Data', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);
$html = '
<table border="1" cellpadding="5">
    <thead>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Volume</th>
            <th>Publication Date</th>
        </tr>
    </thead>
    <tbody>
        <tr><td>1</td><td>Sahel Analyst: Journal of Management Sciences</td><td>Vol. 14</td><td>March, 2016</td></tr>
        <tr><td>2</td><td>Journal of Resources & Economic Development (JRED)</td><td>Vol. 4</td><td>March 2021</td></tr>
        <tr><td>3</td><td>African Journal of Management</td><td>Vol. 8</td><td>March 2021</td></tr>
    </tbody>
</table>
';
$pdf->writeHTML($html, true, false, true, false, '');

// Payments Table
$pdf->Ln(10);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Payments Data', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);
$html = '
<table border="1" cellpadding="5">
    <thead>
        <tr>
            <th>ID</th>
            <th>User ID</th>
            <th>Amount (NGN)</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <tr><td>1</td><td>1</td><td>2.00</td><td>success</td></tr>
        <tr><td>2</td><td>3</td><td>2.00</td><td>success</td></tr>
        <tr><td>3</td><td>3</td><td>2.00</td><td>success</td></tr>
    </tbody>
</table>
';
$pdf->writeHTML($html, true, false, true, false, '');

// Support Inquiries Table
$pdf->Ln(10);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Support Inquiries Data', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);
$html = '
<table border="1" cellpadding="5">
    <thead>
        <tr>
            <th>ID</th>
            <th>User ID</th>
            <th>Subject</th>
            <th>Date Created</th>
        </tr>
    </thead>
    <tbody>
        <tr><td>1</td><td>1</td><td>Eu sint accusamus nesciunt nulla</td><td>2025-06-03 16:10:40</td></tr>
        <tr><td>2</td><td>1</td><td>Animi ex anim officia do laboris sapiente exercitationem hic nulla maiores</td><td>2025-06-04 00:49:18</td></tr>
    </tbody>
</table>
';
$pdf->writeHTML($html, true, false, true, false, '');

// Users Table
$pdf->Ln(10);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Users Data', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);
$html = '
<table border="1" cellpadding="5">
    <thead>
        <tr>
            <th>ID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Email</th>
        </tr>
    </thead>
    <tbody>
        <tr><td>1</td><td>adyems</td><td>Beck</td><td>adyemsgodlove@gmail.com</td></tr>
        <tr><td>2</td><td>Dalton</td><td>Cohen</td><td>xosiz@mailinator.com</td></tr>
        <tr><td>3</td><td>Vladimir</td><td>Hicks</td><td>cevifawu@mailinator.com</td></tr>
    </tbody>
</table>
';
$pdf->writeHTML($html, true, false, true, false, '');

// Admin Table
$pdf->Ln(10);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Admin Data', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);
$html = '
<table border="1" cellpadding="5">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Organization</th>
        </tr>
    </thead>
    <tbody>
        <tr><td>1</td><td>Tucker Jacobs</td><td>meze@mailinator.com</td><td>Hale and Phelps Associates</td></tr>
        <tr><td>2</td><td>Alisa Horne</td><td>mybyq@mailinator.com</td><td>Garcia Daniels LLC</td></tr>
        <tr><td>3</td><td>Beverly Carrillo</td><td>roxugu@mailinator.com</td><td>Hansen Munoz Trading</td></tr>
    </tbody>
</table>
';
$pdf->writeHTML($html, true, false, true, false, '');

// Output PDF
$pdf->Output('database_export.pdf', 'D');
