# 06 - API Design

## Overview

StudentWork exposes a RESTful API built with Laravel.

All endpoints return JSON responses and are protected using Laravel Sanctum where authentication is required.

API Version

```
/api/v1
```

---

# Authentication

Authentication is based on mobile OTP.

Protected endpoints require a valid Sanctum token.

Authorization Header

```
Authorization: Bearer {token}
```

---

# Response Format

Successful Response

```json
{
    "success": true,
    "message": "Operation completed successfully.",
    "data": {}
}
```

Validation Error

```json
{
    "success": false,
    "message": "Validation failed.",
    "errors": {}
}
```

Server Error

```json
{
    "success": false,
    "message": "Internal Server Error."
}
```

---

# Authentication Endpoints

| Method | Endpoint | Description |
|---------|----------|-------------|
| POST | /auth/send-otp | Send OTP code |
| POST | /auth/verify-otp | Verify OTP |
| POST | /auth/logout | Logout |
| GET | /auth/me | Current authenticated user |

---

# User Endpoints

| Method | Endpoint |
|---------|----------|
| GET | /users/me |
| PUT | /users/me |
| POST | /users/avatar |
| POST | /users/resume |
| GET | /users/{id} |

---

# Skill Endpoints

| Method | Endpoint |
|---------|----------|
| GET | /skills |
| POST | /users/skills |
| DELETE | /users/skills/{id} |

---

# Category Endpoints

| Method | Endpoint |
|---------|----------|
| GET | /categories |
| GET | /categories/{id} |

---

# Task Endpoints

| Method | Endpoint |
|---------|----------|
| GET | /tasks |
| POST | /tasks |
| GET | /tasks/{id} |
| PUT | /tasks/{id} |
| DELETE | /tasks/{id} |
| POST | /tasks/{id}/cancel |
| GET | /users/me/tasks |

---

# Task File Endpoints

| Method | Endpoint |
|---------|----------|
| POST | /tasks/{id}/files |
| DELETE | /task-files/{id} |

---

# Application Endpoints

| Method | Endpoint |
|---------|----------|
| POST | /tasks/{id}/applications |
| GET | /tasks/{id}/applications |
| GET | /applications/{id} |
| POST | /applications/{id}/accept |
| POST | /applications/{id}/reject |
| GET | /users/me/applications |

---

# Application File Endpoints

| Method | Endpoint |
|---------|----------|
| POST | /applications/{id}/files |
| DELETE | /application-files/{id} |

---

# Conversation Endpoints

| Method | Endpoint |
|---------|----------|
| POST | /applications/{id}/conversation |
| GET | /conversations/{id} |
| GET | /conversations |

---

# Message Endpoints

| Method | Endpoint |
|---------|----------|
| GET | /conversations/{id}/messages |
| POST | /conversations/{id}/messages |
| PATCH | /messages/{id}/read |

---

# Project Endpoints

| Method | Endpoint |
|---------|----------|
| GET | /projects |
| GET | /projects/{id} |
| PATCH | /projects/{id}/status |

---

# Delivery Endpoints

| Method | Endpoint |
|---------|----------|
| GET | /projects/{id}/deliveries |
| POST | /projects/{id}/deliveries |
| GET | /deliveries/{id} |
| POST | /deliveries/{id}/accept |
| POST | /deliveries/{id}/revision |

---

# Delivery File Endpoints

| Method | Endpoint |
|---------|----------|
| POST | /deliveries/{id}/files |
| GET | /delivery-files/{id}/download |

---

# Complaint Endpoints

| Method | Endpoint |
|---------|----------|
| POST | /projects/{id}/complaints |
| GET | /projects/{id}/complaints |

---

# Review Endpoints

| Method | Endpoint |
|---------|----------|
| POST | /projects/{id}/reviews |
| GET | /users/{id}/reviews |

---

# Notification Endpoints

| Method | Endpoint |
|---------|----------|
| GET | /notifications |
| PATCH | /notifications/{id}/read |
| PATCH | /notifications/read-all |

---

# Admin Endpoints

Prefix

```
/admin
```

Examples

| Method | Endpoint |
|---------|----------|
| GET | /admin/users |
| GET | /admin/tasks |
| GET | /admin/projects |
| GET | /admin/complaints |
| GET | /admin/reviews |
| GET | /admin/categories |
| POST | /admin/categories |
| PUT | /admin/categories/{id} |
| DELETE | /admin/categories/{id} |

---

# HTTP Status Codes

| Code | Description |
|------|-------------|
| 200 | OK |
| 201 | Created |
| 204 | No Content |
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 422 | Validation Error |
| 500 | Internal Server Error |

---

# Pagination

Collection endpoints should support pagination.

Example

```
GET /tasks?page=1
```

---

# Filtering

Supported filters may include:

- Category
- Status
- Price
- Deadline

Example

```
GET /tasks?category=2&status=open
```

---

# Searching

Example

```
GET /tasks?search=Laravel
```

---

# Sorting

Example

```
GET /tasks?sort=created_at
```

Descending

```
GET /tasks?sort=-created_at
```

---

# Authentication Flow

```
Send OTP

↓

Verify OTP

↓

Receive Token

↓

Authenticated Requests

↓

Logout
```

---

# Authorization

Authorization is enforced through:

- Sanctum
- Middleware
- Laravel Policies

Users can only access resources they own unless they are administrators.

---

# File Uploads

Supported uploads:

- Task Files
- Application Files
- Delivery Files
- Avatar
- Resume

Message attachments are not included in the MVP.

---

# API Principles

The API follows these principles:

- RESTful Design
- Resource-Oriented Endpoints
- Consistent JSON Responses
- Stateless Authentication
- Proper HTTP Status Codes
- Validation Before Processing
- Authorization Before Execution

---

# Future API Endpoints

The following endpoints are planned for future releases:

- Wallet APIs
- Payment APIs
- University APIs
- Project Change APIs
- Rating APIs
- Chat Attachment APIs
- WebSocket Events

---

# Summary

The StudentWork API is designed as a RESTful, versioned, and scalable interface that supports all MVP features while remaining extensible for future versions.
