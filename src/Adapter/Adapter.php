<?php

/*
 * This file is part of Gush package.
 *
 * (c) 2013-2014 Luis Cordova <cordoval@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Gush\Adapter;

use Gush\Exception\AdapterException;

/**
 * Adapter is the interface implemented by all Gush Adapter classes.
 *
 * Note that each adapter instance can be only used for one repository.
 */
interface Adapter
{
    /**
     * Returns whether the repository is supported by this adapter.
     *
     * @param string $remoteUrl
     *
     * @return bool
     */
    public function supportsRepository($remoteUrl);

    /**
     * Authenticates the Adapter.
     *
     * @return bool
     */
    public function authenticate();

    /**
     * Returns true if the adapter is authenticated, false otherwise.
     *
     * @return bool
     */
    public function isAuthenticated();

    /**
     * Returns the URL for generating a token.
     *
     * If the adapter doesn't support tokens,
     * this will return null instead.
     *
     * @return null|string
     */
    public function getTokenGenerationUrl();

    /**
     * Creates a fork from upstream and returns an array
     * with the forked url e.g. 'git@github.com:cordoval/repoName.git'
     *
     * @param string $org Organisation name
     *
     * @return array An array the with following keys: git_url, html_url
     *
     * @throws AdapterException when creating a fork failed, eg. not authorized or limit reached
     */
    public function createFork($org);

    /**
     * Gets the information from the requested repository.
     *
     * Returned value must be an array with the following data (values are by example).
     * If a value is not supported null must be used instead, or false in case of a boolean.
     *
     * "owner":         "username"
     * "html_url":      "https://github.com/octocat/Hello-World"
     * "fetch_url":     "https://github.com/octocat/Hello-World.git"
     * "push_url":      "git@github.com:octocat/Hello-World.git"
     * "is_fork":       false
     * "is_private":    false
     * "fork_origin":   [
     *     "org": null
     *     "repo": null
     * ]
     *
     * fork_origin is used to find the original organization and repository.
     * When this repository is the head-parent, "fork_origin" may be null.
     *
     * @param string $org
     * @param string $repository
     *
     * @return array
     */
    public function getRepositoryInfo($org, $repository);

    /**
     * @param $name
     * @param $description
     * @param $homepage
     * @param bool $public
     * @param null $organization
     * @param bool $hasIssues
     * @param bool $hasWiki
     * @param bool $hasDownloads
     * @param int $teamId
     * @param bool $autoInit
     * @return mixed
     */
    public function createRepo(
        $name,
        $description,
        $homepage,
        $public = true,
        $organization = null,
        $hasIssues = true,
        $hasWiki = false,
        $hasDownloads = false,
        $teamId = 0,
        $autoInit = true
    );

    /**
     * Creates a new a comment on an issue/pull-request.
     *
     * @param int    $id
     * @param string $message
     *
     * @return string|null URL to the comment ex. "https://github.com/octocat/Hello-World/pull/1347#issuecomment-1
     *
     * @throws AdapterException when creating of command failed (eg. disabled or not authorized)
     */
    public function createComment($id, $message);

    /**
     * Gets comments of a pull-request..
     *
     * Returned value must be an array with the following data per entry (values are by example).
     * If a value is not supported null must be used instead.
     *
     * "id":         1
     * "url":        "https://github.com/octocat/Hello-World/pull/1347#issuecomment-1"
     * "body":       "Me too"
     * "user":       "username"
     * "created_at": "DateTime Object"
     * "updated_at": "DateTime Object"
     *
     * @param int $id
     *
     * @return array[] [['id' => 1, ...]]
     */
    public function getComments($id);

    /**
     * Gets the supported labels.
     *
     * When the issue tracker does not support labels,
     * this will return an empty array.
     *
     * @return string[]
     */
    public function getLabels();

    /**
     * Gets the supported milestones.
     *
     * @param array $parameters
     *
     * @return string[]
     */
    public function getMilestones(array $parameters = []);

    /**
     * Opens a new pull-request.
     *
     * @param string $base
     * @param string $head
     * @param string $subject
     * @param string $body
     * @param array  $parameters
     *
     * @return array An array the with following keys: html_url, number
     *
     * @throws AdapterException when the pull request are disabled for the repository
     */
    public function openPullRequest($base, $head, $subject, $body, array $parameters = []);

    /**
     * Gets the information of a pull-request by id.
     *
     * Returned value must be an array with the following data (values are by example).
     * If a value is not supported null must be used instead.
     *
     * "url":           "https://github.com/octocat/Hello-World/pull/1"
     * "number":        1
     * "state":         "open"
     * "title":         "new-feature"
     * "body":          "Please pull these awesome changes"
     * "labels":        ["bug"]
     * "milestone":     "v1.0"
     * "created_at":    "DateTime Object"
     * "updated_at":    "DateTime Object"
     * "user":          "username"
     * "assignee":      "username"
     * "merge_commit":  "e5bd3914e2e596debea16f433f57875b5b90bcd6"
     * "merged":        false
     * "merged_by":     "username"
     * "head": [
     *     "ref":   "new-topic"
     *     "sha":   "6dcb09b5b57875f334f61aebed695e2e4193db5e"
     *     "user":  "username"
     *     "repo":  "Hello-World"
     * ]
     * "base": [
     *     "label": "master"
     *     "ref":   "master"
     *     "sha":   "6dcb09b5b57875f334f61aebed695e2e4193db5e"
     *     "user":  "username"
     *     "repo":  "Hello-World"
     * ]
     *
     * @param int $id
     *
     * @return array
     *
     * @throws AdapterException when pull request are disabled for the repository,
     *                          or if the pull request does not exist (anymore)
     */
    public function getPullRequest($id);

    /**
     * Gets the version-commits of a pull-request.
     *
     * Returned value must be an array with the following data per entry (values are by example).
     * If a value is not supported null must be used instead.
     *
     * 'sha':     '6dcb09b5b57875f334f61aebed695e2e4193db5e'
     * 'message': 'Fix all the bugs'
     * 'user':    'username'
     *
     * @param int $id
     *
     * @return array[] [['sha1' => 'dcb09b5b57875f334f61aebed695e2e4193db5e', ...]]
     *
     * @throws AdapterException when pull request are disabled for the repository,
     *                          or if the pull request does not exist (anymore)
     */
    public function getPullRequestCommits($id);

    /**
     * Merges a pull-request by id.
     *
     * @param int    $id
     * @param string $message
     *
     * @return string sha1 of the merge commit
     *
     * @throws AdapterException when merging failed
     */
    public function mergePullRequest($id, $message);

    /**
     * Updates the state of a pull-request by id.
     *
     * @param int   $id
     * @param array $parameters
     *
     * @return void
     *
     * @throws AdapterException when updating of the pull-request failed (eg. disabled or not authorized)
     */
    public function updatePullRequest($id, array $parameters);

    /**
     * Switches the pull-request base.
     *
     * When the adapter does not support changing the base-branch, a new PR should be opened
     * with same subject, body and labels. And the old PR should be closed.
     *
     * @param int    $prNumber
     * @param string $newBase    New base for the PR
     * @param string $newHead    org:branch
     * @param bool   $forceNewPr Open new PR (even when switching is supported)
     *
     * @return array An array with the following keys: html_url, number (either the current or the new PR)
     */
    public function switchPullRequestBase($prNumber, $newBase, $newHead, $forceNewPr = false);

    /**
     * Close a pull-request by id.
     *
     * @param int $id
     *
     * @throws AdapterException when closing of a the pull-request failed (eg. already closed or not authorized)
     */
    public function closePullRequest($id);

    /**
     * Gets the pull-requests.
     *
     * @param string $state   Only get pull-requests with this state (use getPullRequestStates() supported states)
     * @param int    $page
     * @param int    $perPage
     *
     * @return array[] An array where each entry has the same structure as described in getPullRequest()
     *
     * @throws AdapterException when state is unsupported
     */
    public function getPullRequests($state = null, $page = 1, $perPage = 30);

    /**
     * Gets the supported pull-request states.
     *
     * @return string[]
     */
    public function getPullRequestStates();

    /**
     * Creates a new release.
     *
     * For clarity, a release is a tagged version
     * with additional information like a changelog.
     *
     * @param string $name
     * @param array  $parameters
     *
     * @return array Returns an array with url and id of the created release
     */
    public function createRelease($name, array $parameters = []);

    /**
     * Creates a new release asset.
     *
     * An asset can be eg documentation or a full download (library package with vendors).
     * Not every Hub provider supports this however, so implementation is optional.
     *
     * @param int    $id          Id of the release (must exist)
     * @param string $name        Name of the asset (including file extension)
     * @param string $contentType Mime-type of the asset
     * @param string $content     Actual asset (in raw-binary form without conversion)
     *
     * @return int returns the id of the asset
     */
    public function createReleaseAssets($id, $name, $contentType, $content);

    /**
     * Gets all available created-releases.
     *
     * Returned value must be an array with the following data per entry (values are by example).
     * If a value is not supported null must be used instead.
     *
     * "url":           "https://github.com/octocat/Hello-World/releases/v1.0.0"
     * "id":            1
     * "name":          "v1.0.0"
     * "tag_name":      "v1.0.0"
     * "body":          "Description of the release"
     * "draft":         false
     * "prerelease":    false
     * "created_at":    "DateTime Object"
     * "published_at":  "DateTime Object"
     * "user":          "username"
     *
     * @return array[] [['id' => 1, ...]]
     */
    public function getReleases();

    /**
     * Gets all available release assets of an release.
     *
     * Returned value must be an array with the following data per entry (values are by example).
     * If a value is not supported null must be used instead.
     *
     * Note. Size is in bytes, url contains a link to the asset. but may not necessarily
     * download the actual asset. State can be: "uploaded", "empty", or "uploading".
     *
     * "url":           "https://api.github.com/repos/octocat/Hello-World/releases/assets/1"
     * "id":            1
     * "name":          "example.zip"
     * "label":         "short description"
     * "state":         "uploaded"
     * "content_type":  "application/zip"
     * "size":          1024
     * "created_at":    "DateTime Object"
     * "updated_at":    "DateTime Object"
     * "uploader":      "username"
     *
     * @param int $id Id of the release (must exist)
     *
     * @return array[] [['id' => 1, ...]]
     */
    public function getReleaseAssets($id);

    /**
     * Deletes a release.
     *
     * @param int $id
     *
     * @return void
     *
     * @throws AdapterException when deleting of release failed
     */
    public function removeRelease($id);
}
