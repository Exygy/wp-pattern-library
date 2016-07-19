const webpack = require('webpack');

module.exports = () => {
	return {
		entry: {
			'fabricator': './assets/scripts/_fabricator.js',
		},
		output: {
			path: './assets/scripts',
			filename: '[name].js',
		},
		resolve: {
			extensions: ['', '.js'],
		},
		// devtool: 'source-map',
		plugins: [
			new webpack.optimize.OccurenceOrderPlugin(),
			new webpack.DefinePlugin({}),
			new webpack.optimize.DedupePlugin(),
			new webpack.optimize.UglifyJsPlugin({
				minimize: true,
				sourceMap: false,
				compress: {
					warnings: false,
				},
			}),
		],
		module: {
			loaders: [{
				test: /(\.js)/,
				exclude: /(node_modules)/,
				loaders: ['babel']
			}],
		},
	};
};
