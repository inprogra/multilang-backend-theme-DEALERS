const unregisterCoreBlocks = () => {
    const types = wp.blocks.getBlockTypes();
    const core_blocks = types.filter(
        type => type.name.startsWith( 'core/' ) || type.name.startsWith( 'core-embed/' )
    );
    const block_names = core_blocks.map( type => type.name );

    block_names.forEach(block => {
        wp.blocks.unregisterBlockType(block);
    });
}

const unregisterCustomBlocks = () => {
    const blocks = [
        'yoast/faq-block',
        'yoast/how-to-block',
        'yoast-seo/breadcrumbs',
        'filebird/block-filebird-gallery'
    ];

    blocks.forEach(block => {
        wp.blocks.unregisterBlockType(block);
    });
}

wp.domReady(() => {
    unregisterCoreBlocks();
    unregisterCustomBlocks();
});