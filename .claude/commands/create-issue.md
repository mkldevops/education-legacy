---
allowed-tools: Bash(gh:*), mcp__context7__resolve-library-id, mcp__context7__get-library-docs, Task
description: Analyze text and create comprehensive GitHub issue with Symfony best practices
argument-hint: [description-text]
---

# Intelligent Issue Creation with Symfony Context

## Context

    -   Project: Symfony 6.4 education management system
    -   Input text: $1
    -   Current repository: !`gh repo view --json name,description`

## Your task

Create a comprehensive GitHub issue by analyzing the provided text and leveraging Symfony documentation:

### 1. Text Analysis

    -   Parse the input text ($1) to understand the request/problem
    -   Identify the type of issue: feature, bug, enhancement, refactor, etc.
    -   Extract key requirements and technical constraints
    -   Determine affected components/areas of the application

### 2. Symfony Documentation Research

    -   Use Context7 to resolve Symfony library documentation
    -   Research relevant Symfony components for the identified requirements
    -   Find best practices and recommended implementation patterns
    -   Identify potential dependencies or related Symfony features

### 3. Implementation Strategy Analysis

    -   Determine optimal Symfony approach for the requirement
    -   Consider existing project architecture (entities, controllers, services)
    -   Identify required changes to:
        -   Entities and database schema
        -   Controllers and routing
        -   Services and business logic
        -   Forms and validation
        -   Templates and UI components
        -   Tests and fixtures

### 4. Issue Generation

Generate a comprehensive issue with:

**Title**: Clear, concise title following conventional format

    -   Format: `[type]: brief description`
    -   Examples: `feat: add student grade management`, `fix: resolve authentication redirect loop`

**Description**: Detailed description including:

    -   Problem statement or feature request
    -   Acceptance criteria (checkboxes)
    -   Technical implementation suggestions based on Symfony best practices
    -   Affected components and files
    -   Estimated complexity/effort
    -   Dependencies or prerequisites

**Labels**: Appropriate labels based on:

    -   Issue type (feature, bug, enhancement, documentation, etc.)
    -   Priority (low, medium, high, critical)
    -   Affected areas (backend, frontend, database, api, etc.)
    -   Complexity (good-first-issue, complex, breaking-change, etc.)

**Metadata**: Additional metadata:

    -   Assignees (if applicable)
    -   Milestone (if relevant to project roadmap)
    -   Related issues or PRs

### 5. Issue Creation

    -   Create the issue using gh CLI with all generated content
    -   Apply appropriate labels
    -   Set metadata as needed
    -   Output the issue URL for reference

Execute this workflow to transform raw text into a well-structured, technically informed GitHub issue that follows Symfony best practices and project conventions.
