import Button from "./Button";

const ButtonsWrapper = ({title, buttons}) => {
    return <div className='flex flex-col gap-y-4 m-4'>
        <span className=' self-center'>{title}</span>
        <div className='flex gap-x-4'>
            {buttons && buttons.map(button => <Button key={button.title} title={button.title} onClick={button.onClick}/>)}
        </div>
        
    </div>
}

export default ButtonsWrapper;