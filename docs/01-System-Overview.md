# 01 - System Overview

## Project Overview

StudentWork is a university-oriented marketplace that connects students who need help with academic or technical tasks with students who have the required skills to complete them.

Unlike traditional freelance platforms, StudentWork focuses only on university students and provides a simplified workflow that eliminates unnecessary complexity during the MVP phase.

The platform is designed around transparency, simplicity, and scalability.

---

# Project Goals

The main objectives of StudentWork are:

- Connect students through academic work opportunities.
- Simplify project collaboration.
- Provide a structured project workflow.
- Track every stage of a project.
- Keep communication inside the platform.
- Preserve delivery history.
- Allow both parties to review each other after project completion.

---

# MVP Objectives

The MVP focuses only on features required for a functional platform.

Included:

- OTP Authentication
- User Profiles
- Skills
- Categories
- Tasks
- Applications
- Chat
- Projects
- Deliveries
- Complaints
- Reviews
- Notifications

Excluded:

- Wallet
- Online Payment Gateway
- Multi University Support
- Live Chat (WebSocket)
- Project Changes
- Chat File Attachments
- Bidding System

---

# Target Users

StudentWork is designed exclusively for university students.

Each registered user can perform both roles:

- Task Owner
- Task Performer

There are no separate client or freelancer accounts.

---

# User Journey

User Registration

↓

Complete Profile

↓

Add Skills

↓

Create Task
OR
Apply for Existing Task

↓

Task Owner Reviews Applications

↓

One Applicant is Selected

↓

Private Conversation Starts

↓

Project Created

↓

Performer Submits Deliveries

↓

Owner Reviews Submission

↓

Project Completed

↓

Both Users Leave Reviews

---

# System Modules

## Authentication Module

Responsible for:

- Mobile Login
- OTP Verification
- API Authentication

---

## User Module

Responsible for:

- User Profile
- Avatar
- Resume
- Bio
- University Information
- Skills

---

## Skills Module

Responsible for:

- Skill Management
- User Skills
- Skill Matching

---

## Categories Module

Responsible for organizing tasks.

Every task belongs to one category.

---

## Tasks Module

Responsible for:

- Task Creation
- Task Editing
- Task Cancellation
- Task Completion
- Task Files

---

## Applications Module

Responsible for:

- Sending Applications
- Application Files
- Accept / Reject Process

Only one application can be accepted.

---

## Conversation Module

Responsible for private communication.

Conversation becomes available only after the task owner contacts an applicant.

Rejected applicants lose access.

---

## Project Module

Responsible for project lifecycle.

Project Statuses:

- In Progress
- Submitted
- Revision Requested
- Completed
- Cancelled
- Disputed

---

## Delivery Module

Responsible for:

- Submission History
- Version Tracking
- Final Delivery

Every submission is stored permanently.

---

## Complaint Module

Allows both parties to report project problems.

Opening a complaint changes project status to:

Disputed

---

## Review Module

Responsible for mutual reviews after project completion.

Each side can submit:

- Satisfaction
- Unsatisfaction

Optional comment is supported.

---

## Notification Module

Responsible for informing users about important events.

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

# Core Workflow

Task

↓

Applications

↓

Accept One Applicant

↓

Conversation

↓

Project

↓

Deliveries

↓

Completion

↓

Reviews

---

# Authentication Flow

Mobile Number

↓

Send OTP

↓

Verify OTP

↓

Issue Sanctum Token

↓

Authenticated User

---

# User Roles

| Role | Description |
|------|-------------|
| user | Student |
| admin | Platform Administrator |

---

# Project Status Flow

Task

↓

Open

↓

Assigned

↓

Completed

or

Cancelled

or

Expired

---

Project

↓

In Progress

↓

Submitted

↓

Completed

or

Revision Requested

↓

Submitted

↓

Completed

or

Disputed

---

# Database Modules

- Users
- OTP Codes
- Skills
- User Skills
- Categories
- Tasks
- Task Files
- Applications
- Application Files
- Conversations
- Messages
- Projects
- Deliveries
- Delivery Files
- Complaints
- Reviews
- Notifications

---

# Future Versions

The following features are planned for future releases:

- Wallet System
- Online Payments
- Multi University Support
- WebSocket Chat
- Project Change Requests
- Chat File Attachments
- Rating Score
- Recommendation System
- Advanced Search
- Admin Analytics

---

# Summary

StudentWork MVP is designed as a lightweight, scalable, and maintainable platform focused on university students.

The architecture intentionally avoids unnecessary complexity while keeping the codebase ready for future expansion.
