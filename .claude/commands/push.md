---
allowed-tools: Bash(git:*), Bash(gh:*)
description: Add, commit, push and create PR if needed
model: claude-3-5-haiku-20241022
---

# Complete Git workflow

## Context

    -   Current git status: !`git status`
    -   Current git diff (staged and unstaged changes): !`git diff HEAD`
    -   Current branch: !`git branch --show-current`
    -   Recent commits: !`git log --oneline -10`

## Your task

Perform a complete git workflow:

1. Add all changes to staging
2. Analyze changes and generate an appropriate commit message
3. Commit with the generated message
4. Rebase current branch against main: `git rebase main`
5. Push to remote branch (force push if rebase occurred)
6. Check if a PR exists for the current branch
7. If no PR exists, create one automatically

Execute the workflow step by step, generate commit message based on actual changes, and handle any existing PR or create a new one as needed.
