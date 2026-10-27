module.exports = {
  branches: ['main'],
  tagFormat: '${version}',
  plugins: [
    ['@semantic-release/commit-analyzer', { preset: 'conventionalcommits' }],
    ['@semantic-release/release-notes-generator', { preset: 'conventionalcommits' }],
    ['@semantic-release/github', {
      successCommentCondition: false,
      failCommentCondition: false,
      labels: false,
      releasedLabels: false,
      addReleases: false,
    }],
  ],
};
