import React, {PropsWithChildren, ReactNode} from 'react';
import styled from '../styled-with-theme';

export const Section = ({title, children}: PropsWithChildren<{title: ReactNode}>) => (
    <SectionContainer>
        <Title>{title}</Title>
        {children}
    </SectionContainer>
);

const SectionContainer = styled.header`
    border-bottom: 1px solid #11324d;
    display: flex;
    height: 44px;
`;

const Title = styled.div`
    color: ${({theme}) => theme.color.grey140};
    flex-grow: 1;
    font-size: ${({theme}) => theme.fontSize.default};
    line-height: 44px;
    overflow: hidden;
    text-overflow: ellipsis;
    text-transform: uppercase;
    white-space: nowrap;
`;
