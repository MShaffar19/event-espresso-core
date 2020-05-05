import React from 'react';
import classNames from 'classnames';
import { IconButton as ChakraIconButton } from '@chakra-ui/core';

import { IconButtonProps } from './types';

type ButtonType = React.ComponentType<IconButtonProps>;

const IconButton = React.forwardRef<ButtonType, IconButtonProps>(({ variant = 'unstyled', ...props }, ref) => {
	const className = classNames('ee-btn-base', props.className);
	return <ChakraIconButton {...props} className={className} ref={ref} variant={variant} />;
});

export default IconButton;