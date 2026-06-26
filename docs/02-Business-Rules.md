# 02 - Business Rules

## Introduction

This document defines the business rules that govern the StudentWork platform.

Business rules describe how the platform should behave regardless of the implementation details.

These rules must always remain consistent throughout the application.

---

# User Rules

## BR-001

Every user must authenticate using a mobile phone number.

Passwords are not used in the system.

---

## BR-002

Every mobile number must be unique.

Duplicate registrations are not allowed.

---

## BR-003

Each student number must be unique.

Two users cannot register with the same student number.

---

## BR-004

Every user belongs to exactly one university in the MVP version.

Multi-university support is postponed.

---

## BR-005

A user may act as both:

- Task Owner
- Task Performer

No separate account types exist.

---

## BR-006

A user can add multiple skills.

Each skill may belong to multiple users.

---

# Task Rules

## BR-101

Every task must belong to one category.

---

## BR-102

Every task has exactly one owner.

---

## BR-103

Only the task owner may edit or cancel the task.

---

## BR-104

A task accepts multiple applications.

There is no application limit.

---

## BR-105

A task can have only one accepted application.

Accepting one application automatically rejects all remaining pending applications.

---

## BR-106

Rejected users cannot apply again to the same task.

---

## BR-107

A completed, cancelled, assigned or expired task cannot receive new applications.

---

## BR-108

Tasks use a fixed price.

Bidding is not supported.

---

## BR-109

Task attachments are optional.

---

# Application Rules

## BR-201

Every application belongs to exactly one task.

---

## BR-202

Every application belongs to one applicant.

---

## BR-203

A user cannot apply to their own task.

---

## BR-204

A user cannot submit multiple applications for the same task.

---

## BR-205

Application files are optional.

---

## BR-206

Application status can be one of:

- Pending
- Contacted
- Accepted
- Rejected

---

## BR-207

Only the task owner can accept or reject applications.

---

# Conversation Rules

## BR-301

A conversation is created only after the task owner starts communication with an applicant.

---

## BR-302

Only one conversation exists for each application.

---

## BR-303

Rejected applications cannot access conversations.

---

## BR-304

Messages belong only to one conversation.

---

## BR-305

Chat file attachments are not supported in MVP.

---

# Project Rules

## BR-401

A project is created only after an application is accepted.

---

## BR-402

Each accepted application creates exactly one project.

---

## BR-403

Project participants are:

- Task Owner
- Accepted Applicant

No additional participants are allowed.

---

## BR-404

Project status values:

- In Progress
- Submitted
- Revision Requested
- Completed
- Cancelled
- Disputed

---

## BR-405

Only active projects allow deliveries.

---

# Delivery Rules

## BR-501

Every delivery belongs to one project.

---

## BR-502

Only the performer can submit deliveries.

---

## BR-503

Every delivery submission is preserved.

Delivery history cannot be deleted or overwritten.

---

## BR-504

Project owner may:

- Accept Delivery
- Request Revision

---

## BR-505

Requesting revision returns the project to:

Revision Requested

---

## BR-506

Accepting the latest delivery completes the project.

---

# Complaint Rules

## BR-601

A complaint can only be created for an active project.

---

## BR-602

Both project participants may submit complaints.

---

## BR-603

Opening a complaint changes project status to:

Disputed

---

## BR-604

A disputed project cannot be completed until resolved.

---

# Payment Rules

## BR-701

Real payment gateways are not included in MVP.

---

## BR-702

The payment status exists only to simulate payment completion.

Values:

- Unpaid
- Paid

---

## BR-703

Final delivery download becomes available only after payment status changes to Paid.

---

# Review Rules

## BR-801

Reviews become available only after project completion.

---

## BR-802

Each participant can review the other participant exactly once.

---

## BR-803

Review contains:

- Satisfaction
  or
- Unsatisfaction

Optional comment

---

## BR-804

Reviews cannot be edited after submission.

---

# Notification Rules

## BR-901

Notifications are automatically created for important events.

Examples:

- New Application
- Application Accepted
- Application Rejected
- New Message
- Delivery Submitted
- Revision Requested
- Complaint Created
- Review Received

---

## BR-902

Notifications belong to one user.

---

## BR-903

Users can mark notifications as read.

---

# File Rules

## BR-1001

Task files are optional.

---

## BR-1002

Application files are optional.

---

## BR-1003

Delivery files are optional.

---

## BR-1004

Message file attachments are not included in MVP.

---

# Security Rules

## BR-1101

Users can access only their own private conversations.

---

## BR-1102

Users cannot modify resources owned by other users.

---

## BR-1103

Authorization is enforced using Policies and Middleware.

---

## BR-1104

All authenticated APIs require a valid Sanctum token.

---

# Admin Rules

## BR-1201

Administrators can manage all platform resources.

---

## BR-1202

Administrators do not participate in projects as performers.

---

## BR-1203

Administrator permissions are determined by the role field on the users table.

---

# Future Business Rules

The following business rules will be added in future versions:

- Wallet Management
- Real Payment Workflow
- Bidding Rules
- Project Change Requests
- Multiple Universities
- Real-Time Messaging
- Chat Attachments
- Rating Score Calculation

---

# Summary

These business rules define the core behavior of StudentWork MVP.

Any future implementation, API, database schema, or user interface must comply with these rules to ensure consistency across the system.
