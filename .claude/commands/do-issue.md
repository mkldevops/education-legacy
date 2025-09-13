---
allowed-tools: Bash(gh:*), Bash(git:*), Read, Grep, Glob, Task, TodoWrite
description: Resolve GitHub issue end-to-end with implementation and PR
argument-hint: [issue-number]
---

# Complete Issue Resolution Workflow

## Context

- Issue to resolve: $1 (if not provided, will find highest priority issue)
- Current branch: !`git branch --show-current`
- Current status: !`git status --porcelain`

## Your task

Execute a complete issue resolution workflow:

### 1. Issue Analysis

- If issue number provided ($1), fetch details with `gh issue view $1`
- If no issue provided, find highest priority issue with `gh issue list --state open --sort priority`
- Read issue description, requirements, acceptance criteria
- Identify related files, components, and dependencies

### 2. Branch Creation

- Ensure you're on main branch: `git checkout main`
- Pull latest changes: `git pull origin main`
- Create new feature branch from main with descriptive name based on issue
- Example: `git checkout -b feature/issue-123-add-user-authentication`

### 3. Investigation & Planning

- Use TodoWrite to create detailed implementation plan
- Search codebase for related functionality using Grep/Glob
- Read relevant files to understand current implementation
- Identify potential impact areas and breaking changes
- Plan test strategy and validation approach

### 4. Implementation Strategy

- Determine optimal implementation approach
- Consider existing patterns and conventions
- Plan for backwards compatibility
- Identify required dependencies or migrations
- Design for maintainability and performance

### 5. Code Implementation

- Implement solution following project conventions
- Write/update tests as needed
- Ensure code quality standards
- Add documentation if required

### 6. Optimization & Validation

- Run code analysis tools (make analyze)
- Optimize performance bottlenecks
- Validate functionality works as expected
- Check for edge cases and error handling
- Ensure no regressions introduced

### 7. Complete Git Workflow

- Add all changes to staging
- Generate descriptive commit message referencing issue
- Commit changes
- Rebase current branch against main: `git rebase main`
- Push to remote branch (force push if rebase occurred)
- Create/update PR linking to the issue
- Add issue closing keywords to PR description

## Success Criteria

- Issue requirements fully implemented
- All tests pass
- Code quality checks pass
- PR created with proper issue linking
- Implementation is optimized and follows best practices
- Documentation updated if needed

Execute each step systematically, providing updates on progress and any blockers encountered.
