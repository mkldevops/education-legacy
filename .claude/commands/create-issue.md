---
allowed-tools: Bash(gh:*), mcp__context7__resolve-library-id, mcp__context7__get-library-docs, Task
description: Analyze text and create comprehensive GitHub issue with Symfony best practices
argument-hint: [description-text]
---

# Intelligent Issue Creation with Symfony Context

## Context

    -   Project: Symfony 7.3 education management system
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

### 3. Investigation and Solution Research

    -   Research and evaluate available solutions/alternatives
    -   Compare different approaches and their pros/cons
    -   Identify the best solution based on:
        -   Technical compatibility and stability
        -   Maintenance status and community support
        -   Performance and security considerations
        -   Integration complexity with existing codebase
        -   Long-term viability

### 4. Implementation Strategy Analysis

    -   Determine optimal Symfony approach for the requirement
    -   Consider existing project architecture (entities, controllers, services)
    -   Identify required changes to:
        -   Entities and database schema
        -   Controllers and routing
        -   Services and business logic
        -   Forms and validation
        -   Templates and UI components
        -   Tests and fixtures

### 5. Implementation Planning

If a clear best solution is identified during investigation:

    -   Include implementation steps in the issue description
    -   Add specific technical details for the chosen approach
    -   Provide code examples or migration patterns when applicable
    -   Include testing and validation requirements
    -   Add implementation timeline and complexity estimates

### 6. Issue Generation

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

### 7. Issue Creation

    -   Create the issue using gh CLI with all generated content
    -   Apply appropriate labels
    -   Set metadata as needed
    -   Output the issue URL for reference

### 8. Implementation (if applicable)

If investigation reveals a clear, implementable solution:

    -   Ask user for confirmation to proceed with implementation
    -   Implement the identified best solution
    -   Follow the implementation plan defined in the issue
    -   Create appropriate tests and documentation
    -   Ensure code quality standards are met
    -   Update the issue with implementation progress and results

Execute this workflow to transform raw text into a well-structured, technically informed GitHub issue that follows Symfony best practices and project conventions, with optional immediate implementation of the best solution found.
